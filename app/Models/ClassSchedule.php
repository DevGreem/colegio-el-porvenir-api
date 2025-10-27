<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassSchedule extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'course_id',
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'course_id' => 'integer',
            'monday' => 'array',
            'tuesday' => 'array',
            'wednesday' => 'array',
            'thursday' => 'array',
            'friday' => 'array',
        ];
    }

    /**
     * Relationship: owning course.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
