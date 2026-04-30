<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrNew(['email' => 'jtenorio@taurus.com.mx']);
        $user->name = 'J. Tenorio';
        $user->password = 'admin123';
        $user->role = Role::Super;
        $user->email_verified_at = now();
        $user->save();
    }
}
