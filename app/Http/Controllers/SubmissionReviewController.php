<?php

namespace App\Http\Controllers;

use App\Models\ClaimDocument;
use App\Models\ClaimStatusHistory;
use App\Models\ClaimSubmission;
use App\Models\PremiumAdjustmentDocument;
use App\Models\PremiumAdjustmentSubmission;
use App\Models\SubmissionFeedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SubmissionReviewController extends Controller
{
    private function submission(Request $request, string $id, bool $lock = false)
    {
        $broker = $request->routeIs('admin.*') && $request->user()->hasAnyRole(['admin', 'super-admin']);
        $code = $request->user()->company?->CmpCode;
        foreach ([ClaimSubmission::class, PremiumAdjustmentSubmission::class] as $class) {
            $query = $class::where('submission_reference', $id);
            if (! $broker) {
                $query->where('company_code', $code)->when(! $code, fn ($q) => $q->whereRaw('1=0'));
            }
            if ($lock) {
                $query->lockForUpdate();
            }
            if ($record = $query->first()) {
                return $record;
            }
        }
        abort(404);
    }

    public function feedback(Request $request, string $id)
    {
        if ($request->routeIs('admin.*')) {
            abort_unless($request->user()->hasAnyRole(['admin', 'super-admin']), 403);
        }
        $submission = $this->submission($request, $id);
        $input = $request->validate(['message' => ['required', 'string', 'max:5000']]);
        (new SubmissionFeedback)->forceFill([
            $submission instanceof ClaimSubmission ? 'claim_submission_id' : 'premium_adjustment_submission_id' => $submission->id,
            'author_id' => $request->user()->id,
            'message' => $input['message'],
        ])->save();

        return redirect()->route($request->routeIs('admin.*') ? 'admin.submissions.show' : 'client.submissions.show', $submission->submission_reference)
            ->with('status', 'Your message has been sent.');
    }

    public function documents(Request $request, string $id)
    {
        if ($request->routeIs('admin.*')) {
            abort_unless($request->user()->hasAnyRole(['admin', 'super-admin']), 403);
        }
        $submission = $this->submission($request, $id);
        $request->validate([
            'documents' => ['required', 'array', 'max:10'],
            'documents.*' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,txt', 'extensions:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,txt'],
        ]);
        $paths = [];
        try {
            DB::transaction(function () use ($request, $submission, &$paths) {
                foreach ($request->file('documents') as $file) {
                    $path = $file->store('submissions/follow-up/'.$submission->submission_reference, 'local');
                    if (! $path) {
                        throw new \RuntimeException('Could not store document.');
                    }
                    $paths[] = $path;
                    $document = $submission instanceof ClaimSubmission ? new ClaimDocument : new PremiumAdjustmentDocument;
                    $document->forceFill([
                        $submission instanceof ClaimSubmission ? 'claim_submission_id' : 'premium_adjustment_submission_id' => $submission->id,
                        'uploaded_by' => $request->user()->id, 'document_type' => 'supporting_document',
                        'original_name' => mb_substr(basename(str_replace('\\', '/', $file->getClientOriginalName())), 0, 255),
                        'disk' => 'local', 'path' => $path, 'mime_type' => $file->getMimeType(),
                        'size_bytes' => $file->getSize(), 'sha256' => hash_file('sha256', $file->getRealPath()),
                    ])->save();
                }
            });
        } catch (\Throwable $error) {
            Storage::disk('local')->delete($paths);
            report($error);
            throw ValidationException::withMessages(['documents' => 'Could not save your documents. Please select the files again and retry.']);
        }

        return back()->with('status', 'Documents uploaded successfully.');
    }

    public function review(Request $request, string $id)
    {
        abort_unless($request->user()->hasAnyRole(['admin', 'super-admin']), 403);
        $input = $request->validate([
            'portal_status' => ['required', Rule::in(['under_review', 'awaiting_documents', 'accepted', 'rejected'])],
            'previous_status' => ['required', 'string'],
        ]);
        DB::transaction(function () use ($request, $id, $input) {
            $submission = $this->submission($request, $id, true);
            if ($submission->portal_status !== $input['previous_status']) {
                throw ValidationException::withMessages(['portal_status' => 'Another review changed this record. Reload the page before reviewing again.']);
            }
            if ($submission->portal_status === 'draft') {
                throw ValidationException::withMessages(['portal_status' => 'This submission has not been sent by the cedant yet.']);
            }
            $previous = $submission->portal_status;
            $submission->portal_status = $input['portal_status'];
            $submission->save();
            if ($submission instanceof ClaimSubmission && $previous !== $submission->portal_status) {
                (new ClaimStatusHistory)->forceFill([
                    'claim_submission_id' => $submission->id, 'changed_by' => $request->user()->id,
                    'from_status' => $previous, 'to_status' => $submission->portal_status,
                    'remarks' => null, 'changed_at' => now(),
                ])->save();
            }
        });

        return back()->with('status', 'Review status updated.');
    }
}
