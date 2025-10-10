<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\StudentTutor;
use App\Models\Tutor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentTutor>
 */
class StudentTutorFactory extends Factory
{
    protected $model = StudentTutor::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'tutor_id' => Tutor::factory(),
        ];
    }
}
