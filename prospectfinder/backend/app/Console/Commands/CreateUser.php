<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

/**
 * Provisions an account from the command line. This is the only way to create users:
 * the API has no public registration endpoint.
 */
class CreateUser extends Command
{
    protected $signature = 'prospectfinder:create-user {email} {--name=}';

    protected $description = 'Create a ProspectFinder user account (prompts for the password)';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->argument('email')));
        $name = $this->option('name') ?: $this->ask('Name');
        $password = (string) $this->secret('Password');
        $confirmation = (string) $this->secret('Confirm password');

        $validator = Validator::make(
            ['email' => $email, 'name' => $name, 'password' => $password, 'password_confirmation' => $confirmation],
            [
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'name' => ['required', 'string', 'max:255'],
                'password' => ['required', 'string', 'confirmed', 'min:'.config('prospectfinder.auth.minimum_password_length')],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        User::create(['name' => $name, 'email' => $email, 'password' => $password]);
        $this->info("User {$email} created.");

        return self::SUCCESS;
    }
}
