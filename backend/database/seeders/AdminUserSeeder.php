<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Create or update the single admin account from environment config.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => (string) env('ADMIN_EMAIL', 'admin@wall.test')],
            [
                'name' => 'Admin',
                'password' => (string) env('ADMIN_PASSWORD', 'password'),
            ],
        );
    }
}
