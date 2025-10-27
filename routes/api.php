<?php

use App\Models\Course;
use App\Models\ClassSchedule;
use App\Models\Subject;
use App\Models\Student;
use App\Models\Tutor;
use App\Models\User;
use App\Models\UserStudentView;
use App\Support\ScheduleGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

// Home de la api
Route::get('/', function () {
    return response()->json(['welcome' => 'hello']);
});

// Solo es para verificar si funciona la api
Route::get('/health', function () {
    return response()->json(['status' => 'OK']);
});

// Endpoint para logear a los usuarios en el frontend
Route::get('/login', function (Request $request) {
    // Necesita si o si email y password
    $validated = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required', 'string'],
    ]);

    $user = User::where('email', $validated['email'])->first();

    // Checkea si la contraseña es la misma que el hash, sino, devuelve error 401
    if (!$user || !Hash::check($validated['password'], $user->password)) {
        return response()->json([
            'message' => 'Incorrect',
        ], 401);
    }

    // Si es correcta, devuelve Correct y el usuario
    return response()->json([
        'message' => 'Correct',
        'user' => $user,
    ]);
});

$formatUserStudentPayload = static function (UserStudentView $snapshot, ?Student $student = null): array {
    $userPayload = [
        'id' => $snapshot->user_id,
        'name' => $snapshot->user_name,
        'email' => $snapshot->email,
        'user_type' => $snapshot->user_type,
        'email_verified_at' => $snapshot->email_verified_at,
        'created_at' => $snapshot->user_created_at,
        'updated_at' => $snapshot->user_updated_at,
    ];

    $userPayload['student'] = $student
        ? Arr::except($student->toArray(), ['user', 'courseSnapshot'])
        : null;

    return $userPayload;
};

// Endpoint para conseguir a todos los usuarios con o sin filtros
/* Tipos de usuario:
    - SuperAdmin
    - Admin
    - Student
*/
Route::get('/users', function (Request $request) use ($formatUserStudentPayload) {
    // Consigue el tipo de usuario que se busca
    $type = $request->query('type') ?? '';

    $snapshots = UserStudentView::query()
        ->when($type, function ($query, $typeFilter) {
            $query->where('user_type', $typeFilter);
        })
        ->orderBy('user_id')
        ->get();

    $students = Student::query()
        ->with(['tutors', 'course'])
        ->whereIn('id', $snapshots->pluck('student_id')->filter()->unique())
        ->get()
        ->keyBy('id');

    $response = $snapshots
        ->map(function (UserStudentView $snapshot) use ($students, $formatUserStudentPayload) {
            $student = $snapshot->student_id ? $students->get($snapshot->student_id) : null;

            return $formatUserStudentPayload($snapshot, $student);
        })
        ->values();

    return response()->json($response);
});

// Conseguir usuario segun su id
Route::get('/users/{id}', function (int $id) use ($formatUserStudentPayload) {
    $snapshot = UserStudentView::find($id);

    if (!$snapshot) {
        return response()->json(['message' => 'Usuario no encontrado'], 404);
    }

    $student = null;

    if ($snapshot->student_id) {
    $student = Student::with(['tutors', 'course'])->find($snapshot->student_id);
    }

    return response()->json($formatUserStudentPayload($snapshot, $student));
});

// Sube un nuevo usuario
/*
 * Nombre de usuario
 * Correo
 * Password
 * Tipo de usuario
*/
Route::post('/user', function (Request $request) {
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        'password' => ['required', 'string', 'min:8'],
        'user_type' => ['required', 'string', Rule::in(['SuperAdmin', 'Admin', 'Student'])],
    ]);

    // Crea el usuario
    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
        'user_type' => $validated['user_type'],
    ]);

    return response()->json($user, 201);
});

// Sube un nuevo estudiante
Route::post('/student', function (Request $request) use ($formatUserStudentPayload) {
    $validated = $request->validate([
        'user_id' => ['nullable', 'integer', 'exists:users,id'],
        'user' => ['required_without:user_id', 'array'],
        'user.name' => ['required_without:user_id', 'string', 'max:255'],
        'user.email' => ['required_without:user_id', 'email', 'max:255', 'unique:users,email'],
        'user.password' => ['required_without:user_id', 'string', 'min:8'],
        'user.user_type' => ['nullable', 'string', Rule::in(['SuperAdmin', 'Admin', 'Student'])],
        'document' => ['required', 'integer', 'unique:students,document'],
        'first_name' => ['required', 'string', 'max:255'],
        'last_name' => ['required', 'string', 'max:255'],
        'birthdate' => ['nullable', 'date'],
        'gender' => ['nullable', 'string', 'max:20'],
        'course_id' => ['required', 'integer', 'exists:courses,id'],
        'enrollment_date' => ['nullable', 'date'],
        'enrollment_status' => ['nullable', 'string', 'max:25'],
        'phone' => ['nullable', 'string', 'max:30'],
        'tutors' => ['nullable', 'array'],
        'tutors.*.name' => ['required_with:tutors', 'string', 'max:255'],
        'tutors.*.phone' => ['nullable', 'string', 'max:30'],
        'tutors.*.email' => ['nullable', 'email', 'max:255'],
    ]);

    // Si no esta vacio busca el usuario del usuario y lo devuelve
    if (!empty($validated['user_id'])) {


        $user = User::find($validated['user_id']);

        // Si el usuario ya tiene un perfil de estudiante, devuelve 409 avisando que ya existe
        if ($user->student) {
            return response()->json([
                'message' => 'El usuario ya tiene un perfil de estudiante registrado.',
            ], 409);
        }

        // Verifica si el usuario es de tipo Student, sino, lo actualiza (por si hay algun error)
        if ($user->user_type !== 'Student') {
            $user->user_type = 'Student';
            $user->save();
        }
    }
    else {
        // Si el usuario no existe, lo crea para que el estudiante pueda ser creado.

        $userData = $validated['user'];

        $user = User::create([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'password' => Hash::make($userData['password']),
            'user_type' => $userData['user_type'] ?? 'Student',
        ]);
    }


    $studentData = Arr::except($validated, ['user_id', 'user', 'tutors']);
    $studentData['user_id'] = $user->id;
    // Si no devuelven status, pone por defecto activo.
    $studentData['enrollment_status'] = $validated['enrollment_status'] ?? 'Activo';

    $course = Course::find($validated['course_id']);
    if (!$course) {
        return response()->json([
            'message' => 'Curso no encontrado',
        ], 404);
    }

    $studentData['course_id'] = $course->id;

    $student = Student::create($studentData);

    // Si no esta vacio  hace una coleccion de valores de los tutores en el metodo
    if (!empty($validated['tutors'])) {

        // Crea a los tutores
        $tutorIds = collect($validated['tutors'])
            ->map(function (array $tutorData) {
                return Tutor::create($tutorData)->id;
            })
            ->all();

        // Hace que el estudiante este relacionado con esos tutores
        $student->tutors()->sync($tutorIds, false);
    }

    $student->load(['tutors', 'course']);

    $snapshot = UserStudentView::find($student->user_id);

    return response()->json($formatUserStudentPayload($snapshot, $student), 201);
});

Route::patch('/student/{id}', function (Request $request, int $id) use ($formatUserStudentPayload) {
    $validated = $request->validate([
        'enrollment_status' => ['nullable', 'string', 'max:25'],
        'course_id' => ['nullable', 'integer', 'exists:courses,id'],
    ]);

    $student = Student::find($id);

    if (!$student) {
        return response()->json([
            'message' => 'Estudiante no encontrado',
        ], 404);
    }

    $updates = [];

    foreach ($validated as $key => $value) {
        if (!is_null($value) || $key === 'course_id') {
            $updates[$key] = $value;
        }
    }

    // Si no esta vacio, cambia los datos
    if (!empty($updates)) {
        $course = null;

        if (array_key_exists('course_id', $updates)) {
            if (!is_null($updates['course_id'])) {
                $course = Course::find($updates['course_id']);
                if (!$course) {
                    return response()->json([
                        'message' => 'Curso no encontrado',
                    ], 404);
                }
            }
        }

        $student->fill($updates)->save();
    }

    $student = $student->refresh()->load(['tutors', 'course']);

    $snapshot = UserStudentView::find($student->user_id);

    return response()->json($formatUserStudentPayload($snapshot, $student));
});

// Elimina un usuario
Route::delete('/student/{id}', function (int $id) {
    $student = Student::find($id);

    // Si no encuentra el estudiante, devuelve 404
    if (!$student) {
        return response()->json([
            'message' => 'Estudiante no encontrado',
        ], 404);
    }

    // Hace que los tutores dejen de estar relacionados con el estudiante
    $student->tutors()->detach();

    // Finalmente, elimina al estudiante.
    $student->delete();

    return response()->json([
        'message' => 'Estudiante eliminado correctamente',
    ]);
});

Route::get('/courses', function () {
    return Course::with('schedule')
        ->orderBy('grade_level')
        ->orderBy('section')
        ->get();
});

Route::get('/schedules', function (Request $request) {
    $courseId = $request->query('course_id');

    $query = ClassSchedule::query()
        ->with('course');

    if (!empty($courseId)) {
        $query->where('course_id', $courseId);
    }
    $subjects = Subject::pluck('name', 'id');

    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];

    $schedules = $query->orderBy('course_id')->get()
        ->map(function (ClassSchedule $schedule) use ($subjects, $days) {
            $payload = [
                'id' => $schedule->id,
                'course_id' => $schedule->course_id,
                'course' => $schedule->course,
            ];

            foreach ($days as $day) {
                $entries = collect($schedule->{$day} ?? [])
                    ->map(function ($entry) use ($subjects) {
                        if (is_array($entry)) {
                            $hour = $entry['hour'] ?? null;
                            $subjectId = $entry['subject_id'] ?? null;
                        } else {
                            $hour = null;
                            $subjectId = is_numeric($entry) ? (int) $entry : null;
                        }

                        return [
                            'hour' => $hour,
                            'subject' => $subjectId ? ($subjects[$subjectId] ?? null) : null,
                        ];
                    })
                    ->values()
                    ->all();

                $payload[$day] = $entries;
            }

            return $payload;
        });

    return response()->json($schedules);
});

Route::post('/schedules/regenerate', function () {
    $subjects = Subject::all();

    if ($subjects->isEmpty()) {
        collect(config('schedule.subjects', []))->each(function (array $definition) {
            Subject::firstOrCreate(
                ['name' => $definition['name']],
                ['weekly_hours' => $definition['weekly_hours'] ?? 0]
            );
        });

        $subjects = Subject::all();
    }

    if ($subjects->isEmpty()) {
        return response()->json([
            'message' => 'No hay materias disponibles para generar horarios.',
        ], 422);
    }

    $courses = Course::orderBy('grade_level')->orderBy('section')->get();

    if ($courses->isEmpty()) {
        return response()->json([
            'message' => 'No se encontraron cursos para generar horarios.',
        ]);
    }

    $periodsPerDay = (int) config('schedule.periods_per_day', 8);
    $regenerated = 0;

    $courses->each(function (Course $course) use ($subjects, $periodsPerDay, &$regenerated) {
        $plan = ScheduleGenerator::generate($subjects, $periodsPerDay);

        ClassSchedule::updateOrCreate(
            ['course_id' => $course->id],
            $plan
        );

        $regenerated++;
    });

    return response()->json([
        'message' => 'Horarios regenerados correctamente.',
        'courses_processed' => $regenerated,
    ]);
});

?>
