<?php

use App\Models\Student;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

Route::get('/', function () {
    return response()->json(['welcome' => 'hello']);
});

Route::get('/health', function () {
    return response()->json(['status' => 'OK']);
});

Route::get('/login', function (Request $request) {
    $validated = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required', 'string'],
    ]);

    $user = User::where('email', $validated['email'])->first();

    if (!$user || !Hash::check($validated['password'], $user->password)) {
        return response()->json([
            'message' => 'Incorrect',
        ], 401);
    }

    return response()->json([
        'message' => 'Correct',
        'user' => $user,
    ]);
});

// Endpoint para conseguir a todos los usuarios con o sin filtros
/* Tipos de usuario:
    - SuperAdmin
    - Admin
    - Student
*/
Route::get('/users', function (Request $request) {
    // Consigue el tipo de usuario que se busca
    $type = $request->query('type');

    $users = User::query()
        ->with(['student.tutors'])
        ->when($type, function ($query, $typeFilter) {
            $query->where('user_type', $typeFilter);
        })
        ->orderBy('id')
        ->get()
        ->map(function (User $user) {
            if ($user->user_type !== 'Student') {
                $user->setRelation('student', null);
            }

            return $user;
        });

    return response()->json($users);
});

// Conseguir usuario segun su id
Route::get('/users/{id}', function (int $id) {
    $user = User::with('student.tutors')->find($id);

    if (!$user) {
        return response()->json(['message' => 'Usuario no encontrado'], 404);
    }

    return response()->json($user);
});

// Sube un nuevo usuario
/*
 * Nombre de usuario
 * Correo
 *
 *
*/
Route::post('/user', function (Request $request) {
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        'password' => ['required', 'string', 'min:8'],
        'user_type' => ['required', 'string', Rule::in(['SuperAdmin', 'Admin', 'Student'])],
    ]);

    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
        'user_type' => $validated['user_type'],
    ]);

    return response()->json($user, 201);
});

// Sube un nuevo estudiante
Route::post('/student', function (Request $request) {
    $validated = $request->validate([
        'user_id' => ['nullable', 'integer', 'exists:users,id'],
        'user' => ['required_without:user_id', 'array'],
        'user.name' => ['required_without:user_id', 'string', 'max:255'],
        'user.email' => ['required_without:user_id', 'email', 'max:255', 'unique:users,email'],
        'user.password' => ['required_without:user_id', 'string', 'min:8'],
        'user.user_type' => ['nullable', 'string', Rule::in(['SuperAdmin', 'Admin', 'Student'])],
        'document' => ['required', 'string', 'max:50', 'unique:students,document'],
        'first_name' => ['required', 'string', 'max:255'],
        'last_name' => ['required', 'string', 'max:255'],
        'birthdate' => ['nullable', 'date'],
        'gender' => ['nullable', 'string', 'max:20'],
    'grade_level' => ['nullable', 'integer', 'min:1', 'max:12'],
        'section' => ['nullable', 'string', 'max:10'],
        'enrollment_date' => ['nullable', 'date'],
        'enrollment_status' => ['nullable', 'string', 'max:25'],
        'phone' => ['nullable', 'string', 'max:30'],
        'tutors' => ['nullable', 'array'],
        'tutors.*.name' => ['required_with:tutors', 'string', 'max:255'],
        'tutors.*.phone' => ['nullable', 'string', 'max:30'],
        'tutors.*.email' => ['nullable', 'email', 'max:255'],
    ]);

    if (!empty($validated['user_id'])) {
        $user = User::find($validated['user_id']);

        if ($user->student) {
            return response()->json([
                'message' => 'El usuario ya tiene un perfil de estudiante registrado.',
            ], 409);
        }

        if ($user->user_type !== 'Student') {
            $user->user_type = 'Student';
            $user->save();
        }
    } else {
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
    $studentData['enrollment_status'] = $validated['enrollment_status'] ?? 'Activo';

    $student = Student::create($studentData);

    if (!empty($validated['tutors'])) {
        $tutorIds = collect($validated['tutors'])
            ->map(function (array $tutorData) {
                return Tutor::create($tutorData)->id;
            })
            ->all();

        $student->tutors()->sync($tutorIds, false);
    }

    return response()->json($student->load('user', 'tutors'), 201);
});

Route::patch('/student/{student}', function (Request $request, Student $student) {
    $validated = $request->validate([
        'grade_level' => ['nullable', 'integer', 'min:1', 'max:12'],
        'section' => ['nullable', 'string', 'max:10'],
        'enrollment_status' => ['nullable', 'string', 'max:25'],
    ]);

    $student->fill(array_filter($validated, fn ($value) => !is_null($value)));
    $student->save();

    return response()->json($student->fresh()->only([
        'id',
        'grade_level',
        'section',
        'enrollment_status',
    ]));
});

?>
