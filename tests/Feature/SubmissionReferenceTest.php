<?php

namespace Tests\Feature;

use App\Models\ClaimSubmission;
use App\Models\PremiumAdjustmentDocument;
use App\Models\PremiumAdjustmentSubmission;
use App\Models\SubmissionFeedback;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SubmissionReferenceTest extends TestCase
{
    use RefreshDatabase;

    private function submission(string $reference = '', string $company = 'C-001'): ClaimSubmission
    {
        $submission = new ClaimSubmission(['OrigClaimNo' => $reference]);
        $submission->forceFill(['company_code' => $company, 'CoverNo' => 'CV-001'])->save();

        return $submission;
    }

    public function test_references_are_generated_or_preserved_without_a_type_selector(): void
    {
        $first = $this->submission('   ');
        $second = $this->submission();
        $custom = $this->submission(' CLIENT-REF ');
        $this->assertStringStartsWith('CLM-', $first->OrigClaimNo);
        $this->assertLessThanOrEqual(50, strlen($first->OrigClaimNo));
        $this->assertNotSame($first->OrigClaimNo, $second->OrigClaimNo);
        $this->assertSame('CLIENT-REF', $custom->OrigClaimNo);
        $custom->save();
        $this->assertSame('CLIENT-REF', $custom->fresh()->OrigClaimNo);
        $this->assertFalse(Schema::hasColumn('claim_submissions', 'submission_type'));
        $this->assertTrue($custom->requiresDocuments());
    }

    public function test_submitted_references_cannot_change(): void
    {
        $submission = $this->submission('FIXED');
        $submission->forceFill(['submitted_at' => now()])->save();
        $submission->OrigClaimNo = 'CHANGED';
        $this->expectException(ValidationException::class);
        $submission->save();
    }

    public function test_reference_uniqueness_is_per_company(): void
    {
        $this->submission('SAME', 'C-001');
        $this->submission('SAME', 'C-002');
        $this->expectException(QueryException::class);
        $this->submission('SAME', 'C-001');
    }

    public function test_adjustment_documents_and_feedback_are_separate_from_claims(): void
    {
        $claim = $this->submission();
        $adjustment = new PremiumAdjustmentSubmission(['details' => 'Premium adjustment documents']);
        $adjustment->forceFill(['company_code' => 'C-001'])->save();
        $this->assertStringStartsWith('PA-', $adjustment->adjustment_reference);
        $this->assertFalse(Schema::hasColumn('premium_adjustment_submissions', 'submission_type'));
        $doc = new PremiumAdjustmentDocument;
        $doc->forceFill([
            'premium_adjustment_submission_id' => $adjustment->id,
            'original_name' => 'adjustment.pdf', 'disk' => 'local', 'path' => 'private/adjustment.pdf',
            'mime_type' => 'application/pdf', 'size_bytes' => 123,
        ])->save();
        (new SubmissionFeedback(['message' => 'Please review the claim']))->forceFill([
            'claim_submission_id' => $claim->id, 'author_id' => 1,
        ])->save();
        (new SubmissionFeedback(['message' => 'Adjustment received']))->forceFill([
            'premium_adjustment_submission_id' => $adjustment->id, 'author_id' => 1,
        ])->save();
        $this->assertSame('Please review the claim', $claim->feedback->sole()->message);
        $this->assertSame('Adjustment received', $adjustment->feedback->sole()->message);
        $this->assertSame($adjustment->id, $doc->submission->id);
        $this->assertCount(1, $adjustment->documents);
        $this->assertArrayNotHasKey('path', $doc->toArray());
    }

    public function test_feedback_cannot_target_both_modules(): void
    {
        $feedback = new SubmissionFeedback(['message' => 'Invalid target']);
        $feedback->forceFill(['claim_submission_id' => 1, 'premium_adjustment_submission_id' => 1, 'author_id' => 1]);
        $this->expectException(ValidationException::class);
        $feedback->save();
    }

    public function test_new_tables_roll_back(): void
    {
        foreach ([
            '2026_09_30_090002_create_submission_feedback_table.php',
            '2026_09_30_090001_create_premium_adjustment_documents_table.php',
            '2026_09_30_090000_create_premium_adjustment_submissions_table.php',
        ] as $name) {
            (require database_path('migrations/'.$name))->down();
        }
        $this->assertFalse(Schema::hasTable('submission_feedback'));
        $this->assertFalse(Schema::hasTable('premium_adjustment_documents'));
        $this->assertFalse(Schema::hasTable('premium_adjustment_submissions'));
    }
}
