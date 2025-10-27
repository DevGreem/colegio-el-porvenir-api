<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
            $table->unsignedTinyInteger('grade_level')->nullable();
            $table->string('section', 10)->nullable();
            $table->date('enrollment_date')->nullable();
            $table->string('enrollment_status', 25)->default('Activo');
            $table->string('phone', 30)->nullable();
            $table->timestamps();
            $table->foreignId('user_id')->references('id')->on('users');
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

        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::unprepared(<<<'SQL'
                CREATE OR REPLACE FUNCTION enforce_grade_level()
                RETURNS trigger AS
                $$
                BEGIN
                    IF NEW.grade_level IS NOT NULL AND (NEW.grade_level < 1 OR NEW.grade_level > 12) THEN
                        RAISE EXCEPTION 'El grado debe estar entre 1 y 12';
                    END IF;

                    RETURN NEW;
                END;
                $$ LANGUAGE plpgsql;
            SQL);

            DB::unprepared(<<<'SQL'
                CREATE TRIGGER enforce_grade_level_insert
                BEFORE INSERT ON students
                FOR EACH ROW
                EXECUTE FUNCTION enforce_grade_level();
            SQL);

            DB::unprepared(<<<'SQL'
                CREATE TRIGGER enforce_grade_level_update
                BEFORE UPDATE ON students
                FOR EACH ROW
                EXECUTE FUNCTION enforce_grade_level();
            SQL);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::unprepared('DROP TRIGGER IF EXISTS enforce_grade_level_update ON students');
            DB::unprepared('DROP TRIGGER IF EXISTS enforce_grade_level_insert ON students');
            DB::unprepared('DROP FUNCTION IF EXISTS enforce_grade_level()');
        }

        Schema::dropIfExists('students');
        Schema::dropIfExists('student_tutors');
        Schema::dropIfExists('tutors');

        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique(['user_id']);

            $table->dropColumn([
                'first_name',
                'last_name',
                'birthdate',
                'gender',
                'grade_level',
                'section',
                'enrollment_date',
                'enrollment_status',
                'phone',
                'created_at',
                'updated_at',
            ]);

            $table->dropPrimary();
            $table->dropColumn('id');
        });
    }
};
