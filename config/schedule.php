<?php

return [
    'periods_per_day' => 8,

    'time_slots' => [
        '06:00',
        '07:00',
        '08:00',
        '09:00',
        '10:00',
        '11:00',
        '12:00',
        '13:00',
    ],

    'recess' => [
        'subject_name' => 'Recreo',
        'time' => '10:00',
    ],

    'day_start_time' => '06:00',
    'period_minutes' => 60,

    'subjects' => [
        ['name' => 'Matemáticas', 'weekly_hours' => 5],
        ['name' => 'Comunicación', 'weekly_hours' => 5],
        ['name' => 'Ciencias Naturales', 'weekly_hours' => 4],
        ['name' => 'Historia y Geografía', 'weekly_hours' => 3],
        ['name' => 'Educación Física', 'weekly_hours' => 2],
        ['name' => 'Arte y Cultura', 'weekly_hours' => 2],
        ['name' => 'Inglés', 'weekly_hours' => 4],
        ['name' => 'Tecnología', 'weekly_hours' => 3],
        ['name' => 'Tutoría', 'weekly_hours' => 2],
        ['name' => 'Recreo', 'weekly_hours' => 1],
    ],
];
