<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'weekly_hours',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'weekly_hours' => 'integer',
        ];
    }
}
