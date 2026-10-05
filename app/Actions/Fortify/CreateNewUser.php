<?php

namespace App\Actions\Fortify;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Spatie\Permission\Models\Role;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function create(array $input): User
    {
        if (isset($input['company_code']) && is_string($input['company_code'])) {
            $input['company_code'] = trim($input['company_code']);
        }
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
            'password' => $this->passwordRules(),
            'company_code' => [
                'required', 'string', 'max:50',
                Rule::exists('companies', 'CmpCode')->where('CmpSubTypeCode', '100'),
            ],
        ], [
            'company_code.required' => 'Enter the company code provided by your broker.',
            'company_code.exists' => 'This code is not registered as a cedant company. Please contact your broker.',
        ])->validate();

        return DB::transaction(function () use ($input) {
            $company = Company::cedants()->where('CmpCode', $input['company_code'])->first();
            if (! $company) {
                throw ValidationException::withMessages(['company_code' => 'This cedant company is no longer available.']);
            }
            $user = new User([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
            ]);
            // Public input cannot choose a role or another company's database ID.
            $user->company()->associate($company);
            $user->save();
            $user->assignRole(Role::firstOrCreate(['name' => 'client', 'guard_name' => 'web']));

            return $user;
        });
    }
}
