<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Perfil del estudiante
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto space-y-6 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        Información general
                    </h3>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nombre completo</dt>
                            <dd class="mt-1 text-base text-gray-900 dark:text-gray-100">
                                {{ $student->first_name }} {{ $student->last_name }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Documento</dt>
                            <dd class="mt-1 text-base text-gray-900 dark:text-gray-100">
                                {{ $student->document }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha de nacimiento</dt>
                            <dd class="mt-1 text-base text-gray-900 dark:text-gray-100">
                                {{ optional($student->birthdate)->format('d/m/Y') ?? 'Sin registrar' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Género</dt>
                            <dd class="mt-1 text-base text-gray-900 dark:text-gray-100">
                                {{ $student->gender ?? 'Sin registrar' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Grado</dt>
                            <dd class="mt-1 text-base text-gray-900 dark:text-gray-100">
                                {{ $student->grade_level ? 'Grado ' . $student->grade_level : 'Sin registrar' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Sección</dt>
                            <dd class="mt-1 text-base text-gray-900 dark:text-gray-100">
                                {{ $student->section ?? 'Sin registrar' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Estado de matrícula</dt>
                            <dd class="mt-1 text-base text-gray-900 dark:text-gray-100">
                                {{ $student->enrollment_status ?? 'Sin registrar' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha de matrícula</dt>
                            <dd class="mt-1 text-base text-gray-900 dark:text-gray-100">
                                {{ optional($student->enrollment_date)->format('d/m/Y') ?? 'Sin registrar' }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        Información de contacto
                    </h3>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Teléfono del estudiante</dt>
                            <dd class="mt-1 text-base text-gray-900 dark:text-gray-100">
                                {{ $student->phone ?? 'Sin registrar' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Correo del estudiante</dt>
                            <dd class="mt-1 text-base text-gray-900 dark:text-gray-100">
                                {{ optional($student->user)->email ?? 'Sin registrar' }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        Tutores registrados
                    </h3>
                </div>
                <div class="p-6">
                    @if ($student->tutors->isEmpty())
                        <p class="text-base text-gray-900 dark:text-gray-100">No hay tutores registrados.</p>
                    @else
                        <div class="space-y-4">
                            @foreach ($student->tutors as $tutor)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                    <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $tutor->name }}
                                    </h4>
                                    <dl class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Teléfono</dt>
                                            <dd class="mt-1 text-base text-gray-900 dark:text-gray-100">
                                                {{ $tutor->phone ?? 'Sin registrar' }}
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Correo</dt>
                                            <dd class="mt-1 text-base text-gray-900 dark:text-gray-100">
                                                {{ $tutor->email ?? 'Sin registrar' }}
                                            </dd>
                                        </div>
                                    </dl>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        Cuenta del sistema
                    </h3>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Usuario asociado</dt>
                            <dd class="mt-1 text-base text-gray-900 dark:text-gray-100">
                                {{ optional($student->user)->name ?? 'Sin usuario' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Correo electrónico</dt>
                            <dd class="mt-1 text-base text-gray-900 dark:text-gray-100">
                                {{ optional($student->user)->email ?? 'Sin registrar' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tipo de usuario</dt>
                            <dd class="mt-1 text-base text-gray-900 dark:text-gray-100">
                                {{ optional($student->user)->user_type ?? 'Sin asignar' }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
