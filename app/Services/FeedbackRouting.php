<?php

namespace App\Services;

use App\Models\Department;
use App\Models\FeedbackConversation;
use App\Models\FeedbackTopicRoute;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class FeedbackRouting
{
    public const TOPICS = ['general' => 'General enquiry', 'claims' => 'Claims / claims statement', 'premiums' => 'Premiums / premium statement', 'adjustments' => 'Premium adjustments'];

    public function department(string $topic): ?Department
    {
        $department = FeedbackTopicRoute::with('department')->find($topic)?->department;

        return $department?->is_active ? $department : Department::where('is_default', true)->where('is_active', true)->first();
    }

    public function staff(?int $departmentId = null): Builder
    {
        // Departments are organisational labels, not feedback access boundaries.
        return User::role(['admin', 'super-admin'])->where('access_status', 'approved')->whereNotNull('email_verified_at');
    }

    public function canAccess(User $user, FeedbackConversation $conversation): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']) && $user->access_status === 'approved' && $user->hasVerifiedEmail();
    }

    public function brokerRecipients(FeedbackConversation $conversation): Builder
    {
        $staff = $this->staff();
        $missing = ! $staff->exists();
        if ($conversation->needs_assignment !== $missing) {
            $conversation->needs_assignment = $missing;
            $conversation->save();
        }

        return $staff;
    }
}
