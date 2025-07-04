<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        // Validator::make($input, [
        //     'name' => ['required', 'string', 'max:255'],
        //     'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
        //     'password' => $this->passwordRules(),
        //     'role' => ['required', 'string', Rule::in(['student', 'instructor'])],
        //     'headline' => ['required_if:role,instructor', 'string', 'max:255'],
        //     'website_url' => ['required_if:role,instructor', 'url', 'max:255'],
        // ])->validate();

        // // Use a database transaction to ensure both records are created or none are
        // return DB::transaction(function () use ($input) {
        //     $user = User::create([
        //         'name' => $input['name'],
        //         'email' => $input['email'],
        //         'password' => Hash::make($input['password']),
        //         'role' => $input['role'],
        //     ]);

        //     if ($input['role'] === 'instructor') {
        //         $user->instructorProfile()->create([
        //             'headline' => $input['headline'],
        //             'website_url' => $input['website_url'],
        //             // The 'application_status' will default to 'pending'
        //         ]);
        //     }

        //     return $user;
        // });

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'role' => ['required', 'string', Rule::in(['student', 'instructor'])],
            'headline' => ['required_if:role,instructor', 'string', 'max:255'],
            'website_url' => ['required_if:role,instructor', 'url', 'max:255'],
        ])->validate();

        return DB::transaction(function () use ($input) {
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
            ]);

            if ($input['role'] === 'instructor') {
                // Assign BOTH roles
                $user->assignRole('instructor');
                $user->assignRole('student');

                $user->instructorProfile()->create([
                    'headline' => $input['headline'],
                    'website_url' => $input['website_url'],
                ]);
            } else {
                // Assign only the student role
                $user->assignRole('student');
            }

            return $user;
        });
    }
}
