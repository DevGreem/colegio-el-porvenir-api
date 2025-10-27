<?php

namespace Database\Seeders;

use App\Models\ClassSchedule;
use App\Models\Course;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Support\ScheduleGenerator;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        collect(range(1, 6))->each(function (int $grade) {
            foreach (['A', 'B', 'C'] as $section) {
                Course::firstOrCreate([
                    'grade_level' => $grade,
                    'section' => $section,
                ]);
            }
        });

        $subjectCatalog = collect(config('schedule.subjects', []));

        $subjectCatalog->each(function (array $definition) {
            Subject::firstOrCreate(
                ['name' => $definition['name']],
                ['weekly_hours' => $definition['weekly_hours']]
            );
        });

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

        $valeriaCourse = Course::firstOrCreate([
            'grade_level' => 9,
            'section' => 'B',
        ]);

        Student::factory()->for($studentUser, 'user')->state([
            'document' => 10293847,
            'first_name' => 'Valeria',
            'last_name' => 'Gómez Ramírez',
            'enrollment_status' => 'Activo',
            'enrollment_date' => now()->subYear()->format('Y-m-d'),
            'phone' => '+51 912345678',
            'course_id' => $valeriaCourse->id,
        ])->create();

        Student::factory(10)->create();

        $subjects = Subject::all();
        $periodsPerDay = (int) config('schedule.periods_per_day', 6);

        Course::orderBy('grade_level')->orderBy('section')
            ->get()
            ->each(function (Course $course) use ($subjects, $periodsPerDay) {
                $schedule = ScheduleGenerator::generate($subjects, $periodsPerDay);

                ClassSchedule::updateOrCreate(
                    ['course_id' => $course->id],
                    $schedule
                );
            });
    }
}
