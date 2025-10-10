<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tutor extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'phone',
        'email',
    ];

    /**
     * Relationship: students associated with the tutor.
     */
    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_tutors')
            ->withTimestamps();
    }
}
