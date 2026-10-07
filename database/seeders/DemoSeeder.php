<?php

namespace Database\Seeders;

use App\Models\CedantClaimPayable;
use App\Models\CedantPremium;
use App\Models\Claim;
use App\Models\ClaimDocument;
use App\Models\ClaimStatusHistory;
use App\Models\ClaimSubmission;
use App\Models\Company;
use App\Models\Cover;
use App\Models\CoverReinsurer;
use App\Models\FeedbackConversation;
use App\Models\FeedbackMessage;
use App\Models\PremiumAdjustment;
use App\Models\PremiumAdjustmentDocument;
use App\Models\PremiumAdjustmentSubmission;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('DemoSeeder is only allowed in local or testing environments.');
        }
        foreach ([
            'users', 'roles', 'permissions', 'companies', 'covers', 'cover_reinsurers',
            'claims', 'claim_submissions', 'claim_documents', 'claim_status_histories',
            'premium_adjustment_submissions', 'premium_adjustment_documents',
            'premium_adjustments', 'feedback_conversations', 'feedback_messages', 'cedant_premiums', 'cedant_claim_payables',
        ] as $table) {
            if (! Schema::hasTable($table)) {
                throw new RuntimeException("Missing table {$table}. Run the pending migrations before DemoSeeder.");
            }
        }

        DB::transaction(function () {
            $this->callSilent(RolesSeeder::class);
            $companies = [];
            foreach ([
                ['DEMO-C001', 'Demo Cedar Insurance', '100', '1'],
                ['DEMO-C002', 'Demo Lake Insurance', '100', '1'],
                ['DEMO-R001', 'Demo Horizon Reinsurance', '200', '2'],
                ['DEMO-R002', 'Demo Summit Reinsurance', '200', '2'],
                ['DEMO-R003', 'Demo Valley Reinsurance', '200', '2'],
            ] as [$code, $name, $subtype, $type]) {
                $companies[$code] = $this->put(Company::class, ['CmpCode' => $code], [
                    'CompanyName' => $name, 'ShortName' => substr($code, 5),
                    'CmpSubTypeCode' => $subtype, 'CmpTypeCode' => $type,
                    'CmpSubTypeDesc' => $subtype === '100' ? 'Ceding company' : 'Reinsurers',
                    'CmpTypeDesc' => $type === '1' ? 'Ceding company' : 'Reinsurers',
                    'PostalAdd' => '100 Demo Avenue', 'MTown' => 'Lusaka',
                    'CountryCode' => 'ZM', 'CountryName' => 'Zambia',
                    'MktZoneCode' => '2', 'MktZoneDesc' => 'Africa',
                    'MFax' => '', 'EmailAdd' => strtolower($code).'@example.com',
                    'Website' => 'https://example.com', 'StartDate' => '20260101',
                    'CreatedOn' => '20260101', 'MActive' => 0,
                    'ZoneCode' => 'Z-01', 'ZoneName' => 'LOCAL',
                    'NatAcNo' => $subtype === '100' ? 'C-000' : 'R-000',
                    'NatAcName' => 'DEMO ONLY', 'PinNo' => 'DEMO-PIN',
                    'last_synced_at' => now(),
                ]);
            }
            $super = $this->user('superadmin@example.com', 'Demo Superadmin', 'super-admin');
            $admin = $this->user('admin@example.com', 'Demo Broker Admin', 'admin');
            $cedar = $this->user('client@example.com', 'Demo Cedar Cedant', 'client', $companies['DEMO-C001']);
            $lake = $this->user('client2@example.com', 'Demo Lake Cedant', 'client', $companies['DEMO-C002']);

            $covers = [];
            foreach (range(1, 4) as $i) {
                $company = $companies[$i <= 2 ? 'DEMO-C001' : 'DEMO-C002'];
                $code = sprintf('DEMO-CV%03d', $i);
                $covers[$i] = $this->put(Cover::class, ['CoverNo' => $code], [
                    'RecNo' => $i, 'MRef' => 'DEMO-RISK-'.$i,
                    'MTitle' => 'Demo facultative cover '.$i, 'BizType' => '1',
                    'CusCode' => $company->CmpCode, 'CusName' => $company->CompanyName,
                    'CVStartDate' => '20260101', 'CVEndDate' => '20261231',
                    'CVDocDate' => '20260101', 'CVYear' => '2026',
                    'REDivCode' => 'GR', 'REDivDesc' => 'General Reinsurance',
                    'RETypeCode' => 'FC', 'RETypeDesc' => 'Facultative Reinsurance',
                    'REGrpCode' => 'FP', 'REGrpDesc' => 'Facultative Proportional',
                    'RECatCode' => '', 'RECatDesc' => '', 'RETreatyTypCode' => '', 'RETreatyTypDesc' => '',
                    'REClassCode' => 'MOTOR', 'REClassDesc' => 'Motor Comprehensive',
                    'InsCode' => 'DEMO-I'.$i, 'InsName' => 'Demo Transport '.$i,
                    'CVDetails' => 'Fictional commercial motor cover for portal testing.',
                    'MStatus' => 2, 'RskInsured' => 1000000, 'OrigCoverNo' => $code,
                    'MReinsurers' => '50% Demo Horizon; 30% Demo Summit; 20% Demo Valley',
                    'MPremium' => 10000 * $i, 'MBrokerage' => 1000 * $i,
                    'MCurrency' => $i % 2 ? 'USD' : 'ZMW', 'MShare' => 100,
                    'CashCallLimitAmount' => 25000, 'CashCallExcessOff' => 0,
                    'CoverAmount' => 1000000, 'last_synced_at' => now(),
                ]);
                foreach (['DEMO-R001' => 50, 'DEMO-R002' => 30, 'DEMO-R003' => 20] as $reinCode => $share) {
                    $rein = $companies[$reinCode];
                    $this->put(CoverReinsurer::class, ['CoverNo' => $code, 'DetailID' => (int) substr($reinCode, -1)], [
                        'CedCode' => $company->CmpCode, 'CedName' => $company->CompanyName,
                        'ReinCode' => $reinCode, 'ReinName' => $rein->CompanyName,
                        'ReinPostal' => $rein->PostalAdd, 'ReinTown' => $rein->MTown,
                        'ReinCtryCode' => 'ZM', 'ReinCtry' => 'Zambia',
                        'ReinMktCode' => '2', 'ReinMktDesc' => 'Africa',
                        'ShrAlloc' => $share, 'ShrComp' => 0, 'ShrOpt' => $share,
                        'BrkgComp' => 0, 'MTaxable' => 0, 'MTaxCompShr' => 0, 'MTaxOptShr' => 0,
                        'RskInsured' => 1000000, 'CompAmt' => 0, 'OptAmt' => 10000 * $share,
                        'TotalAmt' => 10000 * $share, 'BrkgOpt' => 10,
                        'CommissionOverWride' => 0, 'BrokerageOverWride' => 0,
                        'BrokerageComp' => 0, 'BrokerageOpt' => 10, 'last_synced_at' => now(),
                    ]);
                }
            }

            $statuses = ['draft', 'submitted', 'awaiting_documents', 'registered_in_rbs', 'rejected', 'registered_in_rbs'];
            foreach ($statuses as $offset => $status) {
                $i = $offset + 1;
                $owner = $i <= 4 ? $cedar : $lake;
                $cover = $covers[$i <= 4 ? (($i % 2) + 1) : (($i % 2) + 3)];
                $company = $owner->company;
                $reference = 'DEMO-ORIG-'.$i;
                $official = $status === 'registered_in_rbs' ? 'DEMO-CL'.$i : null;
                if ($official) {
                    $data = array_intersect_key($cover->getAttributes(), array_flip([
                        'CoverNo', 'CVStartDate', 'CVEndDate', 'CVYear', 'REDivCode', 'REDivDesc',
                        'RETypeCode', 'RETypeDesc', 'REGrpCode', 'REGrpDesc', 'RECatCode', 'RECatDesc',
                        'RETreatyTypCode', 'RETreatyTypDesc', 'REClassCode', 'REClassDesc',
                    ]));
                    $this->put(Claim::class, ['ClaimNo' => $official], array_merge($data, [
                        'ClaimRec' => $i, 'ClaimType' => 'DEMO-FC', 'RelatedNo' => '', 'RelatedRef' => '',
                        'ClaimRef' => 'DEMO-REF-'.$i, 'CoverRef' => $cover->MRef,
                        'CedCode' => $company->CmpCode, 'CedName' => $company->CompanyName,
                        'OrigPolicy' => 'DEMO-POL-'.$i, 'OrigClaimNo' => $reference,
                        'InsuredName' => $cover->InsName, 'LossPeriod' => 'September 2026',
                        'DateLoss' => '20260915', 'DateRegistered' => '20260920',
                        'LossLocation' => 'Lusaka', 'MStatusCode' => $i === 4 ? 'DEMO-OPEN' : 'DEMO-PAID',
                        'MStatusDesc' => $i === 4 ? 'Open (demo)' : 'Paid (demo)',
                        'LossDetails' => 'Fictional vehicle damage.', 'Comments' => 'Demo only, not a real RBS claim.',
                        'CreatedOn' => '20260920', 'CusCode' => $company->CmpCode, 'CusName' => $company->CompanyName,
                        'AcctCode' => $company->CmpCode, 'AcctName' => $company->CompanyName,
                        'AcctPostal' => $company->PostalAdd, 'AcctTown' => $company->MTown,
                        'AcctCtryCode' => 'ZM', 'AcctCtryDesc' => 'Zambia',
                        'AcctMktCode' => '2', 'AcctMktDesc' => 'Africa',
                        'ClaimAmt' => 5000 * $i, 'BrkClaimAmt' => 0, 'TreatyText' => '',
                        'LossReserve' => $i === 4 ? 20000 : 0, 'CedantRetention' => 1000,
                        'DateReported' => '20260916', 'LogicalClaimAmount' => 5000 * $i,
                        'OldDocument' => 0, 'ClaimCurrencyCode' => $cover->MCurrency,
                        'ClaimCurrencyName' => $cover->MCurrency === 'USD' ? 'US Dollar' : 'Zambia Kwacha',
                        'last_synced_at' => now(),
                    ]));
                }
                $submission = $this->put(ClaimSubmission::class, ['company_code' => $company->CmpCode, 'OrigClaimNo' => $reference], [
                    'submitted_by' => $owner->id, 'CoverNo' => $cover->CoverNo,
                    'portal_status' => $status, 'submitted_at' => $status === 'draft' ? null : '2026-09-20 10:00:00',
                    'ClaimNo' => $official, 'linked_by' => $official ? $admin->id : null,
                    'linked_at' => $official ? '2026-09-21 10:00:00' : null,
                    'OrigPolicy' => 'DEMO-POL-'.$i, 'InsuredName' => $cover->InsName,
                    'LossPeriod' => 'September 2026', 'DateLoss' => '20260915',
                    'DateReported' => '20260916', 'LossLocation' => 'Lusaka',
                    'LossDetails' => 'Fictional vehicle damage for demo claim '.$i,
                    'Comments' => 'Demo submission', 'ClaimAmt' => 5000 * $i,
                    'ClaimCurrencyCode' => $cover->MCurrency,
                    'ClaimCurrencyName' => $cover->MCurrency === 'USD' ? 'US Dollar' : 'Zambia Kwacha',
                ]);
                if ($status !== 'draft') {
                    $this->document(ClaimDocument::class, 'claim_submission_id', $submission->id, $owner->id, 'claim-'.$i);
                    $this->put(ClaimStatusHistory::class, ['claim_submission_id' => $submission->id, 'to_status' => 'submitted', 'changed_at' => '2026-09-20 10:00:00'], [
                        'from_status' => 'draft', 'changed_by' => $owner->id, 'remarks' => 'Demo claim submitted.',
                    ]);
                    if ($status !== 'submitted') {
                        $this->put(ClaimStatusHistory::class, ['claim_submission_id' => $submission->id, 'to_status' => $status, 'changed_at' => '2026-09-21 10:00:00'], [
                            'from_status' => 'submitted', 'changed_by' => $admin->id, 'remarks' => 'Demo broker review.',
                        ]);
                    }
                }
            }

            foreach (range(1, 3) as $i) {
                $owner = $i < 3 ? $cedar : $lake;
                $existingDemo = PremiumAdjustmentDocument::where('path', 'demo/reinsurance/adjustment-'.$i.'.txt')->first()?->submission;
                $adjustment = $this->put(PremiumAdjustmentSubmission::class, [
                    'submission_reference' => $existingDemo?->submission_reference ?? '01J0000000000000000000000'.$i,
                ], [
                    'company_code' => $owner->company->CmpCode, 'CoverNo' => $covers[$i]->CoverNo, 'submitted_by' => $owner->id,
                    'portal_status' => $i === 1 ? 'submitted' : 'under_review',
                    'details' => 'Demo premium adjustment following a revised risk schedule.',
                    'submitted_at' => '2026-09-22 10:00:00',
                ]);
                $this->put(PremiumAdjustment::class, ['DocumentNo' => 'DEMO-ADJ-'.$i, 'ItemNo' => '1'], [
                    'CoverNo' => $covers[$i]->CoverNo, 'CedantAccountNo' => $owner->company->CmpCode,
                    'Cedant' => $owner->company->CompanyName, 'DocumentDate' => '2026-09-22',
                    'DocCurrency' => $covers[$i]->MCurrency, 'ItemDesc' => 'Premium adjustment',
                    'MinDepositPremium' => 10000, 'NetAccountedPremium' => 12000, 'PremiumComputed' => 2000,
                    'PremiumAdjRate' => 5, 'Posted' => 0, 'Committed' => 1, 'last_synced_at' => now(),
                ]);
                $this->document(PremiumAdjustmentDocument::class, 'premium_adjustment_submission_id', $adjustment->id, $owner->id, 'adjustment-'.$i);
            }

            foreach (range(1, 6) as $i) {
                $cover = $covers[(($i - 1) % 4) + 1];
                $amount = $i * 10000;
                $settled = $i % 3 === 0 ? $amount : ($i % 3 === 1 ? 0 : $amount / 2);
                $common = [
                    'DocumentDate' => '2026-09-20 00:00:00', 'DocumentDateNo' => 20260920,
                    'AccountTypeCode' => 'DEMO-TYPE', 'MAmount' => $amount,
                    'SettledAmount' => $settled, 'PendingAmount' => $amount - $settled,
                    'CurrencyCode' => $cover->MCurrency,
                    'CurrencyName' => $cover->MCurrency === 'USD' ? 'US Dollar' : 'Zambia Kwacha',
                    'ExchangeRate' => $cover->MCurrency === 'USD' ? 25 : 1,
                    'FunctionalAmount' => $amount * ($cover->MCurrency === 'USD' ? 25 : 1),
                    'RiskNoteRef' => $cover->MRef, 'Insured' => $cover->InsName,
                    'RiskDetails' => 'Demo commercial motor', 'LastUser' => 'DEMO',
                    'LastMaintained' => '2026-09-22 00:00:00', 'LastMaintainedNo' => 20260922,
                    'CategoryType' => 'FC', 'CategoryName' => 'Facultative Reinsurance',
                    'CedantCode' => $cover->CusCode, 'CedantName' => $cover->CusName,
                    'DocumentTypeCode' => 'DEMO-DOC', 'PercentPaid' => $settled / $amount * 100,
                    'CollectionRemark' => 'Demo only', 'Reversed' => 0, 'ReversingDoc' => '',
                    'CRate' => 1, 'last_synced_at' => now(),
                ];
                $this->put(CedantPremium::class, ['DocumentNo' => 'DEMO-DN'.$i], array_merge($common, [
                    'AccountType' => 'Gross Premium', 'DocumentType' => 'Risk Note',
                    'Brokerage' => $amount * 0.1, 'ReinsurersAmount' => $amount * 0.9,
                ]));
                $this->put(CedantClaimPayable::class, ['DocumentNo' => 'DEMO-CP'.$i], array_merge($common, [
                    'AccountType' => 'Claim payable', 'DocumentType' => 'Claim statement',
                    'DOL' => '2026-09-15 00:00:00', 'DOLNo' => 20260915,
                    'ClaimRefReinsured' => 'DEMO-PAY-REF'.$i,
                    // Do not invent a ClaimReference -> ClaimNo mapping.
                    'ClaimReference' => 'DEMO-STMT-REF'.$i, 'RequisitionNo' => 'DEMO-REQ'.$i,
                ]));
            }
            foreach ([
                ['DEMO-HELP-001', $cedar, 'How can I update my contact details?', 'Please help me update our office contact email.', 'Please send the new email address and we will guide you.'],
                ['DEMO-HELP-002', $cedar, 'Statement download question', 'How can I access my statement of account?', 'Open Statement of Account from your portal menu.'],
                ['DEMO-HELP-003', $lake, 'Portal feedback', 'Thank you. Could you explain where to find my covers?', 'Select My Policies / Covers from your home page.'],
            ] as [$reference, $owner, $subject, $question, $answer]) {
                $conversation = FeedbackConversation::where('reference', $reference)->first();
                if (! $conversation) {
                    $conversation = (new FeedbackConversation)->forceFill(['reference' => $reference, 'subject' => $subject, 'created_by' => $owner->id, 'company_code' => $owner->company?->CmpCode]);
                    $conversation->save();
                }
                foreach ([[$owner->id, $question], [$admin->id, $answer]] as [$author, $message]) {
                    $this->put(FeedbackMessage::class, ['feedback_conversation_id' => $conversation->id, 'author_id' => $author, 'message' => $message], []);
                }
            }
        });
        $this->command?->info('Demo data and feedback conversations seeded. New demo accounts use password; existing passwords are preserved.');
    }

    private function put(string $class, array $key, array $values): Model
    {
        $record = $class::query()->where($key)->first() ?? new $class;
        $record->forceFill(array_merge($key, $values))->save();

        return $record;
    }

    private function user(string $email, string $name, string $role, ?Company $company = null): User
    {
        $user = User::firstOrCreate(['email' => $email], [
            'name' => $name, 'password' => Hash::make('password'), 'email_verified_at' => now(),
        ]);
        $user->company()->associate($company);
        $user->save();
        $user->assignRole($role);

        return $user;
    }

    private function document(string $class, string $key, int $submissionId, int $userId, string $label): void
    {
        $path = 'demo/reinsurance/'.$label.'.txt';
        $content = "DEMO DOCUMENT ONLY\nFictional supporting information for {$label}.\n";
        if (! Storage::disk('local')->exists($path) && ! Storage::disk('local')->put($path, $content)) {
            throw new RuntimeException('Could not write demo document: '.$path);
        }
        $this->put($class, [$key => $submissionId, 'path' => $path], [
            'uploaded_by' => $userId, 'document_type' => 'supporting_document',
            'original_name' => $label.'.txt', 'disk' => 'local', 'mime_type' => 'text/plain',
            'size_bytes' => Storage::disk('local')->size($path),
            'sha256' => hash('sha256', Storage::disk('local')->get($path)),
        ]);
    }
}
