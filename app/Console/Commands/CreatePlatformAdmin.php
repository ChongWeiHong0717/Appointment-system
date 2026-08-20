<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreatePlatformAdmin extends Command
{
    protected $signature = 'platform:admin:create
        {--name= : Administrator name}
        {--email= : Administrator email}
        {--password= : Password (omit to enter it securely)}';

    protected $description = 'Create a business-independent platform administrator account';

    public function handle(): int
    {
        $name = (string) ($this->option('name') ?: $this->ask('Administrator name'));
        $email = str((string) ($this->option('email') ?: $this->ask('Administrator email')))->lower()->toString();
        $password = (string) ($this->option('password') ?: $this->secret('Password'));
        $confirmation = $this->option('password') ? $password : (string) $this->secret('Confirm password');

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $confirmation,
        ], [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(12)],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        User::query()->create([
            'business_id' => null,
            'name' => $name,
            'email' => $email,
            'role' => UserRole::PlatformAdmin,
            'password' => $password,
            'is_active' => true,
        ]);

        $this->info('Platform administrator created. Sign in at /platform/login.');

        return self::SUCCESS;
    }
}
