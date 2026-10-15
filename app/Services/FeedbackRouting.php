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

    public function staff(?int $departmentId): Builder
    {
        return User::role('admin')->where('department_id', $departmentId)->whereNotNull('department_id')
            ->where('access_status', 'approved')->whereNotNull('email_verified_at')->whereHas('department', fn ($q) => $q->where('is_active', true));
    }

    public function canAccess(User $user, FeedbackConversation $conversation): bool
    {
        return $user->hasRole('super-admin') || ($user->hasRole('admin') && $conversation->department_id !== null
            && (int) $user->department_id === (int) $conversation->department_id && $conversation->department?->is_active);
    }

    public function brokerRecipients(FeedbackConversation $conversation): Builder
    {
        $staff = $this->staff($conversation->department_id);
        $missing = ! $staff->exists();
        if ($conversation->needs_assignment !== $missing) {
            $conversation->needs_assignment = $missing;
            $conversation->save();
        }

        return $missing ? User::role('super-admin')->where('access_status', 'approved')->whereNotNull('email_verified_at') : $staff;
    }
}
