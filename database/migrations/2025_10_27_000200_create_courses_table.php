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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('grade_level');
            $table->string('section', 10);
            $table->timestamps();

            $table->unique(['grade_level', 'section']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('course_id')
                ->nullable()
                ->constrained('courses')
                ->nullOnDelete()
                ->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropConstrainedForeignId('course_id');
        });
        Schema::dropIfExists('courses');
    }
};
