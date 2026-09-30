<?php

namespace Tests\Feature;

use App\Models\Claim;
use App\Models\ClaimDocument;
use App\Models\ClaimStatusHistory;
use App\Models\ClaimSubmission;
use App\Models\Company;
use App\Models\Cover;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ClaimSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_multiple_unlinked_submissions_can_have_no_claim_number(): void
    {
        $first = $this->submission();
        $second = $this->submission();

        $this->assertNull($first->ClaimNo);
        $this->assertNull($second->ClaimNo);
        $this->assertNotSame($first->submission_reference, $second->submission_reference);
        $this->assertTrue($first->requiresDocuments());
        $this->assertTrue($second->requiresDocuments());
        $this->assertSame('draft', $first->fresh()->portal_status);
        $this->assertSame('123.45678901', $first->fresh()->ClaimAmt);
    }

    public function test_claim_can_arrive_after_submission_without_overwriting_portal_status(): void
    {
        $submission = $this->submission();
        $submission->forceFill(['ClaimNo' => 'CL-001', 'portal_status' => 'under_review'])->save();
        $this->assertNull($submission->claim);

        Claim::create([
            'ClaimNo' => 'CL-001', 'CoverNo' => 'COVER-001', 'CedCode' => 'C-001',
            'MStatusCode' => 'RBS-STATUS', 'MStatusDesc' => 'Source status', 'ClaimAmt' => 999,
        ]);

        $loaded = $submission->fresh(['claim']);
        $this->assertSame('CL-001', $loaded->claim->ClaimNo);
        $this->assertSame('under_review', $loaded->portal_status);
        $this->assertSame('RBS-STATUS', $loaded->claim->MStatusCode);
        $this->assertSame('123.45678901', $loaded->ClaimAmt);
        $this->assertSame(999.0, $loaded->claim->ClaimAmt);
        foreach (['MStatusCode', 'ClaimRec', 'AcctCode', 'BrkClaimAmt', 'LossReserve', 'RETypeCode'] as $field) {
            $this->assertFalse(Schema::hasColumn('claim_submissions', $field));
            $this->assertTrue(Schema::hasColumn('claims', $field));
        }
        $this->assertSame($submission->id, $loaded->claim->submissions->first()->id);
        $this->assertNull($loaded->company);
        $this->assertNull($loaded->cover);

        Company::create(['CmpCode' => 'C-001', 'CmpSubTypeCode' => '100']);
        (new Cover)->forceFill(['CoverNo' => 'COVER-001', 'CusCode' => 'C-001'])->save();

        $loaded = $submission->fresh(['company', 'cover', 'claim.cedant', 'claim.cover']);
        $this->assertSame('C-001', $loaded->company->CmpCode);
        $this->assertSame('COVER-001', $loaded->cover->CoverNo);
        $this->assertSame('C-001', $loaded->claim->cedant->CmpCode);
        $this->assertSame('COVER-001', $loaded->claim->cover->CoverNo);
    }

    public function test_documents_and_history_remain_on_the_portal_submission(): void
    {
        $submission = $this->submission();
        $document = new ClaimDocument;
        $document->forceFill([
            'claim_submission_id' => $submission->id,
            'original_name' => 'claim.pdf', 'disk' => 'local',
            'path' => 'claims/example.pdf', 'mime_type' => 'application/pdf',
            'size_bytes' => 42,
        ])->save();
        (new ClaimStatusHistory)->forceFill([
            'claim_submission_id' => $submission->id,
            'from_status' => 'draft', 'to_status' => 'submitted',
            'remarks' => 'Submitted by cedant', 'changed_at' => now(),
        ])->save();

        $this->assertCount(1, $submission->documents);
        $this->assertSame('submitted', $submission->statusHistories->first()->to_status);
        $this->assertSame($submission->id, $document->submission->id);
        $this->assertArrayNotHasKey('path', $document->toArray());
        $this->assertArrayNotHasKey('disk', $document->toArray());
        foreach (['ClaimNo', 'company_code', 'portal_status', 'submitted_by', 'linked_by'] as $field) {
            $this->assertFalse($submission->isFillable($field));
        }
    }

    public function test_duplicate_mirrored_claim_numbers_are_rejected(): void
    {
        Claim::create(['ClaimNo' => 'CL-001']);
        $this->expectException(QueryException::class);
        Claim::create(['ClaimNo' => 'CL-001']);
    }

    public function test_claim_migrations_roll_back_and_source_links_have_no_foreign_keys(): void
    {
        foreach (['claims', 'claim_submissions', 'claim_documents', 'claim_status_histories'] as $table) {
            $this->assertSame([], Schema::getForeignKeys($table));
        }
        foreach ([
            '2026_09_29_071720_create_claim_status_histories_table.php',
            '2026_09_29_071716_create_claim_documents_table.php',
            '2026_09_29_071712_create_claim_submissions_table.php',
            '2026_09_29_071725_create_claims_table.php',
        ] as $file) {
            (require database_path('migrations/'.$file))->down();
        }
        $this->assertFalse(Schema::hasTable('claims'));
        $this->assertFalse(Schema::hasTable('claim_submissions'));
        $this->assertFalse(Schema::hasTable('claim_documents'));
        $this->assertFalse(Schema::hasTable('claim_status_histories'));
    }

    private function submission(): ClaimSubmission
    {
        $submission = new ClaimSubmission([
            'InsuredName' => 'Example insured',
            'LossDetails' => 'Example loss', 'ClaimAmt' => '123.45678901',
        ]);
        $submission->forceFill(['company_code' => 'C-001', 'CoverNo' => 'COVER-001'])->save();

        return $submission;
    }
}
