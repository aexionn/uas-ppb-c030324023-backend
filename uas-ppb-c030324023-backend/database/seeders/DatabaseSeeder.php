<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Program;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Account::firstOrCreate(
            ['username' => 'admin'],
            [
                'role' => 'admin',
                'nisn' => null,
                'email' => 'admin@campus.test',
                'password' => 'password',
            ]
        );

        foreach (['Teknik Informatika', 'Sistem Informasi', 'Manajemen', 'Akuntansi'] as $name) {
            Program::firstOrCreate(['name' => $name]);
        }
    }
}
