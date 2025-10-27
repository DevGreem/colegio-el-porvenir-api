<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id()->first();
            $table->integer('document')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->date('birthdate')->nullable();
            $table->string('gender', 20)->nullable();
            $table->date('enrollment_date')->nullable();
            $table->string('enrollment_status', 25)->default('Activo');
            $table->string('phone', 30)->nullable();
            $table->timestamps();
            $table->foreignId('user_id')->constrained('users');
        });

        Schema::create('tutors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });

        Schema::create('student_tutors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tutor_id')->constrained('tutors')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['student_id', 'tutor_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_tutors');
        Schema::dropIfExists('tutors');
        Schema::dropIfExists('students');
    }
};
