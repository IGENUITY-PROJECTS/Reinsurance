<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\FeedbackConversation;
use App\Models\FeedbackTopicRoute;
use App\Services\FeedbackRouting;
use Illuminate\Database\Seeder;

class DepartmentsSeeder extends Seeder
{
    public function run(): void
    {
        $department = Department::firstOrCreate(['code' => 'general'], ['name' => 'General', 'is_default' => true, 'is_active' => true]);
        foreach (array_keys(FeedbackRouting::TOPICS) as $topic) {
            FeedbackTopicRoute::firstOrCreate(['topic' => $topic], ['department_id' => $department->id]);
        }
        // Keep existing conversations available to the default team during the upgrade.
        FeedbackConversation::whereNull('department_id')->update(['department_id' => $department->id]);
    }
}
