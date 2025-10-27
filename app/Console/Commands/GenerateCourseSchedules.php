<?php

namespace App\Console\Commands;

use App\Models\ClassSchedule;
use App\Models\Course;
use App\Models\Subject;
use App\Support\ScheduleGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class GenerateCourseSchedules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedules:generate {course_id? : Regenerate the schedule for a specific course}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate automatic weekly schedules for one or all courses.';

    public function handle(): int
    {
        $courseId = $this->argument('course_id');
        $subjects = $this->resolveSubjects();

        if ($subjects->isEmpty()) {
            $this->error('No hay materias definidas. Agrega materias antes de generar los horarios.');

            return self::FAILURE;
        }

        $periodsPerDay = (int) config('schedule.periods_per_day', 6);

        $courses = $courseId
            ? Course::whereKey($courseId)->get()
            : Course::orderBy('grade_level')->orderBy('section')->get();

        if ($courses->isEmpty()) {
            $this->warn('No se encontraron cursos para generar horarios.');

            return self::SUCCESS;
        }

        $generated = 0;

        foreach ($courses as $course) {
            $plan = ScheduleGenerator::generate($subjects, $periodsPerDay);

            ClassSchedule::updateOrCreate(
                ['course_id' => $course->id],
                $plan
            );

            $this->line(sprintf('Horario generado para %s %s', $course->grade_level, $course->section));
            $generated++;
        }

        $this->info(sprintf('Se generaron/actualizaron %d horarios.', $generated));

        return self::SUCCESS;
    }

    /**
     * @return Collection<int, Subject>
     */
    protected function resolveSubjects(): Collection
    {
        $subjects = Subject::all();

        if ($subjects->isNotEmpty()) {
            return $subjects;
        }

        $catalog = collect(config('schedule.subjects', []));

        return $catalog->map(function (array $definition) {
            return Subject::firstOrCreate(
                ['name' => $definition['name']],
                ['weekly_hours' => $definition['weekly_hours']]
            );
        });
    }
}
