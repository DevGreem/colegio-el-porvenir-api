<?php

use App\Models\Student;
use App\Models\Tutor;
use App\Models\User;
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
Route::post('/student', function (Request $request) {

    // Valida si cada parametro cumple los requisitos
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

    // Devuelve los datos del nuevo estudiante
    return response()->json($student->load('user', 'tutors'), 201);
});

Route::patch('/student/{id}', function (Request $request, int $id) {

    // Valida si estan esos parametros en el request
    $validated = $request->validate([
        'grade_level' => ['nullable', 'integer', 'min:1', 'max:12'],
        'section' => ['nullable', 'string', 'max:10'],
        'enrollment_status' => ['nullable', 'string', 'max:25'],
    ]);

    $student = Student::find($id);

    if (!$student) {
        return response()->json([
            'message' => 'Estudiante no encontrado',
        ], 404);
    }

    // Hace una conexion con los valores dados que no sean null
    $updates = collect($validated)
        ->filter(fn ($value) => !is_null($value))
        ->all();

    // Si no esta vacio, cambia los datos
    if (!empty($updates)) {
        $student->fill($updates)->save();
    }

    // Devuelve el estudiante actualizado
    return response()->json($student->refresh()->load('user', 'tutors'));
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

?>
