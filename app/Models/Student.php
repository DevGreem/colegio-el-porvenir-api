<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tutor;
use App\Models\Course;

class Student extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'document',
        'first_name',
        'last_name',
        'birthdate',
        'gender',
        'enrollment_date',
        'enrollment_status',
        'phone',
        'course_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
            'enrollment_date' => 'date',
            'course_id' => 'integer',
        ];
    }

    /**
     * Relationship: owning user account.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: tutors associated with the student.
     */
    public function tutors()
    {
        return $this->belongsToMany(Tutor::class, 'student_tutors')
            ->using(StudentTutor::class)
            ->withTimestamps();
    }

    /**
     * Relationship: course assigned to the student.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Relationship: read-only course metadata exposed via database view.
     */
    public function courseSnapshot()
    {
        return $this->hasOne(StudentCourseView::class, 'student_id');
    }
}
