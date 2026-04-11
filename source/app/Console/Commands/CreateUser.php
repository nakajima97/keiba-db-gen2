<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:create-user')]
#[Description('Command description')]
class CreateUser extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->ask('Email');

        if (User::where('email', $email)->exists()) {
            $this->error('User already exists');

            return;
        }

        $user = User::create([
            'name' => $this->ask('Name'),
            'email' => $email,
            'password' => Hash::make($this->secret('Password')),
        ]);

        $this->info("User created: {$user->email}");
    }
}
