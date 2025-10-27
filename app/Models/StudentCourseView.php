<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentCourseView extends Model
{
    /**
     * The database table associated with the model.
     */
    protected $table = 'student_course_view';

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'student_id';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = false;

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = [];
}
