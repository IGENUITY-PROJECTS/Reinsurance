<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;
use Throwable;

class CreatePortalSuperadmin extends Command
{
    protected $signature = 'portal:superadmin';

    protected $description = 'Create a superadmin securely without a seeded password';

    public function handle(): int
    {
        if (! Role::where('name', 'super-admin')->where('guard_name', 'web')->exists()) {
            $this->error('Run ProductionRolesSeeder first.');

            return self::FAILURE;
        }
        $input = ['name' => $this->ask('Name'), 'email' => mb_strtolower(trim((string) $this->ask('Email'))),
            'password' => $this->secret('Password (12+ characters, uppercase, lowercase, number and symbol)'), 'password_confirmation' => $this->secret('Confirm password')];
        $validator = Validator::make($input, ['name' => 'required|string|max:255', 'email' => 'required|email|max:255|unique:users,email', 'password' => ['required', 'confirmed', Password::default()]]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

return self::FAILURE;
        }
        $user = DB::transaction(function () use ($input) {
            $user = new User(collect($input)->only(['name', 'email', 'password'])->all());
            $user->access_status = 'approved';
            $user->approved_at = now();
            $user->email_verified_at = null;
            $user->save();
            $user->assignRole('super-admin');

            return $user;
        });
        try {
            $user->sendEmailVerificationNotification();
            $this->info('Superadmin created. A verification email has been queued.');
        } catch (Throwable $error) {
            report($error);
            $this->warn('Superadmin created. Email could not be queued; sign in and resend verification after fixing mail/queue settings.');
        }

        return self::SUCCESS;
    }
}
