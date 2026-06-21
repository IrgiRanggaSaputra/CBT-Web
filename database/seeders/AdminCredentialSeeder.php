<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class AdminCredentialSeeder extends Seeder
{
    /**
     * Seed a default admin account for local development.
     */
    public function run(): void
    {
        Admin::updateOrCreate(
            ['username' => 'admin'],
            [
                'password' => 'Admin@12345',
                'nama_lengkap' => 'Administrator CBT',
                'email' => 'admin@cbt.local',
            ],
        );
    }
}
