<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use function fake;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        $firstName = $this->faker->firstName();
        $lastName = $this->faker->lastName();

        return [
            'user_id' => User::factory(),
            'document' => (string) $this->faker->unique()->numerify('########'),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'birthdate' => $this->faker->dateTimeBetween('-18 years', '-6 years')->format('Y-m-d'),
            'gender' => $this->faker->randomElement(['Masculino', 'Femenino', 'Otro']),
            'grade_level' => $this->faker->numberBetween(1, 12),
            'section' => $this->faker->randomElement(['A', 'B', 'C']),
            'enrollment_date' => $this->faker->dateTimeBetween('-3 years', 'now')->format('Y-m-d'),
            'enrollment_status' => $this->faker->randomElement(['Activo', 'Suspendido', 'Egresado']),
            'phone' => $this->faker->numerify('+51 9#######'),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Student $student) {
            $tutors = Tutor::factory()
                ->count(fake()->numberBetween(1, 2))
                ->create();

            $student->tutors()->attach($tutors->pluck('id'));
        });
    }
}
