<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class StudentTutor extends Pivot
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'student_id',
        'tutor_id',
    ];

    /**
     * Relationship: owning student.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Relationship: owning tutor.
     */
    public function tutor()
    {
        return $this->belongsTo(Tutor::class);
    }
}
