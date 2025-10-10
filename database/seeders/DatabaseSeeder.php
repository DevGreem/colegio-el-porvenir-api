<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $superAdmin = User::factory()->create([
            'name' => 'María Fernanda Ríos',
            'email' => 'superadmin@porvenir.edu',
            'user_type' => 'SuperAdmin',
            'password' => 'password',
        ]);

        $admin = User::factory()->create([
            'name' => 'Juan Carlos Herrera',
            'email' => 'admin@porvenir.edu',
            'user_type' => 'Admin',
            'password' => 'password',
        ]);

        $studentUser = User::factory()->create([
            'name' => 'Valeria Gómez',
            'email' => 'valeria.gomez@porvenir.edu',
            'user_type' => 'Student',
            'password' => 'password',
        ]);

        Student::factory()->for($studentUser, 'user')->state([
            'document' => '10293847',
            'first_name' => 'Valeria',
            'last_name' => 'Gómez Ramírez',
            'grade_level' => 9,
            'section' => 'B',
            'enrollment_status' => 'Activo',
            'enrollment_date' => now()->subYear()->format('Y-m-d'),
            'phone' => '+51 912345678',
        ])->create();

        Student::factory(10)->create();
    }
}
