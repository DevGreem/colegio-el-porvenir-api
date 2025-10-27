<?php

namespace App\Models;

use App\Models\Subject;
use App\Support\ScheduleGenerator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'grade_level',
        'section',
    ];

    protected static function booted(): void
    {
        static::created(function (Course $course) {
            if ($course->schedule()->exists()) {
                return;
            }

            $subjects = Subject::all();

            if ($subjects->isEmpty()) {
                return;
            }

            $periodsPerDay = (int) config('schedule.periods_per_day', 6);
            $schedule = ScheduleGenerator::generate($subjects, $periodsPerDay);

            $course->schedule()->create($schedule);
        });
    }

    /**
     * Relationship: students enrolled in the course.
     */
    public function students()
    {
        return $this->hasMany(Student::class);
    }

    /**
     * Relationship: schedule assigned to the course.
     */
    public function schedule()
    {
        return $this->hasOne(ClassSchedule::class);
    }
}
