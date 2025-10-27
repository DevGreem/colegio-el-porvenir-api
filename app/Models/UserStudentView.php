<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserStudentView extends Model
{
    /**
     * The database table associated with the model.
     */
    protected $table = 'user_student_view';

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'user_id';

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
