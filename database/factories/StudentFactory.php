<?php

namespace Database\Factories;

use App\Models\ClassSchedule;
use App\Models\Course;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Tutor;
use App\Models\User;
use App\Support\ScheduleGenerator;
use Illuminate\Database\Eloquent\Factories\Factory;
use function fake;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    protected static bool $coursePoolInitialized = false;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'document' => $this->faker->unique()->numberBetween(10000000, 99999999),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'birthdate' => $this->faker->dateTimeBetween('-18 years', '-6 years')->format('Y-m-d'),
            'gender' => $this->faker->randomElement(['Masculino', 'Femenino', 'Otro']),
            'enrollment_date' => $this->faker->dateTimeBetween('-3 years', 'now')->format('Y-m-d'),
            'enrollment_status' => $this->faker->randomElement(['Activo', 'Suspendido', 'Egresado']),
            'phone' => $this->faker->numerify('+51 9#######'),
            'course_id' => null,
        ];
    }

    public function configure(): static
    {
        return $this
            ->afterCreating(function (Student $student) {
                if (!$student->course_id) {
                    self::ensureCoursePool();

                    $course = Course::inRandomOrder()->first();

                    if ($course) {
                        $student->course()->associate($course);
                        $student->save();
                    }
                }
            })
            ->afterCreating(function (Student $student) {
                $tutors = Tutor::factory()
                    ->count(fake()->numberBetween(1, 2))
                    ->create();

                $student->tutors()->attach($tutors->pluck('id'));
            });
    }

    protected static function ensureCoursePool(): void
    {
        if (self::$coursePoolInitialized) {
            return;
        }

        $courseIds = [];

        foreach (range(1, 12) as $grade) {
            $sectionCount = fake()->numberBetween(1, 3);
            $maxSection = chr(ord('A') + $sectionCount - 1);

            foreach (range('A', $maxSection) as $section) {
                $course = Course::firstOrCreate([
                    'grade_level' => $grade,
                    'section' => $section,
                ]);

                $courseIds[] = $course->id;
            }
        }

        $subjects = Subject::all();

        if ($subjects->isEmpty()) {
            $subjects = collect(config('schedule.subjects', []))
                ->map(function (array $definition) {
                    return Subject::firstOrCreate(
                        ['name' => $definition['name']],
                        ['weekly_hours' => $definition['weekly_hours']]
                    );
                });

            $subjects = Subject::all();
        }

        if ($subjects->isNotEmpty()) {
            $periodsPerDay = (int) config('schedule.periods_per_day', 6);

            Course::whereIn('id', $courseIds)
                ->get()
                ->each(function (Course $course) use ($subjects, $periodsPerDay) {
                    $plan = ScheduleGenerator::generate($subjects, $periodsPerDay);

                    ClassSchedule::updateOrCreate(
                        ['course_id' => $course->id],
                        $plan
                    );
                });
        }

        self::$coursePoolInitialized = true;
    }
}
