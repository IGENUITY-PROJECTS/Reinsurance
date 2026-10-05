<?php

namespace App\Http\Controllers;

use App\Models\ClaimDocument;
use App\Models\ClaimStatusHistory;
use App\Models\ClaimSubmission;
use App\Models\Cover;
use App\Models\PremiumAdjustmentDocument;
use App\Models\PremiumAdjustmentSubmission;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class CedantSubmissionController extends Controller
{
    private function covers(Request $request, string $module)
    {
        $code = $request->user()->company?->CmpCode;

        return Cover::query()->where('CusCode', $code)
            ->when(! $code, fn ($q) => $q->whereRaw('1=0'))
            ->when($module === 'claims', fn ($q) => $q->where('RETypeCode', 'FC'));
    }

    private function coverFields(): array
    {
        return ['CoverNo', 'MRef', 'InsName', 'CVStartDate', 'CVEndDate', 'MCurrency'];
    }

    public function create(Request $request)
    {
        $module = $request->route()->defaults['module'] ?? 'claims';
        $company = $request->user()->company;
        $covers = $this->covers($request, $module)->orderBy('CoverNo')->limit(30)->get($this->coverFields());
        $selectedCode = $request->old('CoverNo', $request->query('cover', ''));
        $selectedCover = is_string($selectedCode)
            ? $this->covers($request, $module)->where('CoverNo', $selectedCode)->first($this->coverFields())
            : null;
        if ($selectedCover && ! $covers->contains('CoverNo', $selectedCover->CoverNo)) {
            $covers->prepend($selectedCover);
        }
        $token = $request->old('_submission_token');
        if (! is_string($token) || ! Str::isUlid($token)) {
            $token = (string) Str::ulid();
        }

        return view('client-records.create-submission', [
            'module' => $module, 'company' => $company, 'covers' => $covers,
            'selectedCover' => $selectedCover, 'token' => $token,
            'formTitle' => $module === 'claims' ? 'New claim' : 'New premium adjustment',
        ]);
    }

    public function coverOptions(Request $request)
    {
        abort_unless($request->user()->company, 403);
        $input = $request->validate(['module' => 'required|in:claims,adjustments', 'q' => 'nullable|string|max:150']);
        $query = $this->covers($request, $input['module']);
        if (! empty($input['q'])) {
            $term = '%'.$input['q'].'%';
            $query->where(fn ($q) => $q->where('CoverNo', 'like', $term)->orWhere('MRef', 'like', $term)->orWhere('InsName', 'like', $term));
        }

        return response()->json($query->orderBy('CoverNo')->limit(30)->get($this->coverFields()));
    }

    public function store(Request $request)
    {
        $module = $request->route()->defaults['module'] ?? 'claims';
        $isClaim = $module === 'claims';
        $company = $request->user()->company;
        abort_unless($company && $company->CmpSubTypeCode === '100', 403, 'A cedant company must be linked to your account.');
        $model = $isClaim ? ClaimSubmission::class : PremiumAdjustmentSubmission::class;
        $referenceField = 'OrigClaimNo';
        if (is_string($request->input($referenceField))) {
            $request->merge([$referenceField => trim($request->input($referenceField)) ?: null]);
        }
        $coverRule = Rule::exists('covers', 'CoverNo')->where('CusCode', $company->CmpCode);
        if ($isClaim) {
            $coverRule->where('RETypeCode', 'FC');
        }
        $rules = [
            '_submission_token' => ['required', 'ulid'],
            'CoverNo' => ['required', 'string', 'max:50', $coverRule],
            'documents' => [$isClaim ? 'required' : 'nullable', 'array', 'max:10'],
            'documents.*' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,txt', 'extensions:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,txt'],
        ];
        if ($isClaim) {
            $rules += [
                'OrigClaimNo' => ['nullable', 'string', 'max:50'],
                'DateLoss' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
                'LossLocation' => ['nullable', 'string', 'max:250'],
                'LossDetails' => ['required', 'string', 'max:2000'],
                'ClaimAmt' => ['nullable', 'regex:/^\d{1,16}(\.\d{1,8})?$/'],
            ];
        } else {
            $rules['details'] = ['required', 'string', 'max:10000'];
        }
        $input = $request->validate($rules, [
            'CoverNo.exists' => 'Choose one of your company’s available covers.',
            'documents.required' => 'Attach at least one supporting document.',
            'ClaimAmt.regex' => 'Enter a non-negative amount with up to 8 decimal places.',
        ]);
        // One form token identifies one submission, including a double click/retry.
        $existing = $model::where('submission_reference', $input['_submission_token'])->first();
        if ($existing) {
            return $this->existing($request, $existing, $company->CmpCode);
        }
        $reference = $input[$referenceField] ?? null;
        if ($reference && $model::where('company_code', $company->CmpCode)->where($referenceField, $reference)->exists()) {
            throw ValidationException::withMessages([$referenceField => 'Your company already has a submission with this reference.']);
        }

        $paths = [];
        try {
            $submission = DB::transaction(function () use ($request, $input, $company, $module, $isClaim, $model, &$paths) {
                $cover = $this->covers($request, $module)->where('CoverNo', $input['CoverNo'])->first();
                if (! $cover) {
                    throw ValidationException::withMessages(['CoverNo' => 'This cover is no longer available to your company.']);
                }
                $submission = new $model;
                $submission->forceFill([
                    'submission_reference' => $input['_submission_token'],
                    'company_code' => $company->CmpCode,
                    'submitted_by' => $request->user()->id,
                    'CoverNo' => $cover->CoverNo,
                    'portal_status' => 'submitted',
                    'submitted_at' => now(),
                ]);
                if ($isClaim) {
                    $submission->forceFill([
                        'OrigClaimNo' => $input['OrigClaimNo'] ?? null,
                        'InsuredName' => $cover->InsName,
                        'DateLoss' => str_replace('-', '', $input['DateLoss']),
                        'DateReported' => now()->format('Ymd'),
                        'LossLocation' => $input['LossLocation'] ?? null,
                        'LossDetails' => $input['LossDetails'],
                        'ClaimAmt' => $input['ClaimAmt'] ?? null,
                        'ClaimCurrencyCode' => $cover->MCurrency,
                    ]);
                } else {
                    $submission->details = $input['details'];
                }
                $submission->save();
                foreach ($request->file('documents', []) as $file) {
                    $path = $file->store('submissions/'.$module.'/'.$submission->submission_reference, 'local');
                    if (! $path) {
                        throw new \RuntimeException('Could not save the uploaded file.');
                    }
                    $paths[] = $path;
                    $document = $isClaim ? new ClaimDocument : new PremiumAdjustmentDocument;
                    $document->forceFill([
                        $isClaim ? 'claim_submission_id' : 'premium_adjustment_submission_id' => $submission->id,
                        'uploaded_by' => $request->user()->id,
                        'document_type' => 'supporting_document',
                        'original_name' => mb_substr(basename(str_replace('\\', '/', $file->getClientOriginalName())), 0, 255),
                        'disk' => 'local', 'path' => $path,
                        'mime_type' => $file->getMimeType(), 'size_bytes' => $file->getSize(),
                        'sha256' => hash_file('sha256', $file->getRealPath()),
                    ])->save();
                }
                if ($isClaim) {
                    (new ClaimStatusHistory)->forceFill([
                        'claim_submission_id' => $submission->id, 'changed_by' => $request->user()->id,
                        'from_status' => null, 'to_status' => 'submitted', 'changed_at' => now(),
                        'remarks' => 'Claim submitted through the cedant portal.',
                    ])->save();
                }

                return $submission;
            });
        } catch (Throwable $error) {
            Storage::disk('local')->delete($paths);
            // Handle concurrent requests without duplicating the submission.
            if ($error instanceof QueryException) {
                $existing = $model::where('submission_reference', $input['_submission_token'])->first();
                if ($existing) {
                    return $this->existing($request, $existing, $company->CmpCode);
                }
                if ($reference && $model::where('company_code', $company->CmpCode)->where($referenceField, $reference)->exists()) {
                    throw ValidationException::withMessages([$referenceField => 'Your company already has a submission with this reference.']);
                }
            }
            if ($error instanceof ValidationException) {
                throw $error;
            }
            report($error);
            throw ValidationException::withMessages(['documents' => 'We could not save your submission. Please select the files again and retry.']);
        }

        return redirect()->route('client.submissions.show', $submission->submission_reference)
            ->with('status', ($isClaim ? 'Claim' : 'Premium adjustment').' submitted successfully. '.($isClaim ? 'Reference: '.$submission->OrigClaimNo : 'Cover: '.$submission->CoverNo));
    }

    private function existing(Request $request, $submission, string $companyCode)
    {
        abort_unless($submission->company_code === $companyCode && (int) $submission->submitted_by === (int) $request->user()->id, 403);

        return redirect()->route('client.submissions.show', $submission->submission_reference)
            ->with('status', 'This submission has already been received.');
    }
}
