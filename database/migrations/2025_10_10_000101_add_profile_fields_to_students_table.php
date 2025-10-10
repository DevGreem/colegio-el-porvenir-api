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
        Schema::table('students', function (Blueprint $table) {
            $table->id()->first();
            $table->string('first_name')->after('document');
            $table->string('last_name')->after('first_name');
            $table->date('birthdate')->nullable()->after('last_name');
            $table->string('gender', 20)->nullable()->after('birthdate');
            $table->unsignedTinyInteger('grade_level')->nullable()->after('gender');
            $table->string('section', 10)->nullable()->after('grade_level');
            $table->date('enrollment_date')->nullable()->after('section');
            $table->string('enrollment_status', 25)->default('Activo')->after('enrollment_date');
            $table->string('phone', 30)->nullable()->after('enrollment_status');
            $table->timestamps();

            $table->unique('user_id');
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
        } elseif ($driver === 'sqlite') {
            DB::unprepared(<<<'SQL'
                CREATE TRIGGER enforce_grade_level_insert
                BEFORE INSERT ON students
                FOR EACH ROW
                BEGIN
                    SELECT
                        CASE
                            WHEN NEW.grade_level IS NOT NULL AND (NEW.grade_level < 1 OR NEW.grade_level > 12) THEN
                                RAISE(ABORT, 'El grado debe estar entre 1 y 12')
                        END;
                END;
            SQL);

            DB::unprepared(<<<'SQL'
                CREATE TRIGGER enforce_grade_level_update
                BEFORE UPDATE ON students
                FOR EACH ROW
                BEGIN
                    SELECT
                        CASE
                            WHEN NEW.grade_level IS NOT NULL AND (NEW.grade_level < 1 OR NEW.grade_level > 12) THEN
                                RAISE(ABORT, 'El grado debe estar entre 1 y 12')
                        END;
                END;
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
        } elseif ($driver === 'sqlite') {
            DB::unprepared('DROP TRIGGER IF EXISTS enforce_grade_level_update');
            DB::unprepared('DROP TRIGGER IF EXISTS enforce_grade_level_insert');
        }

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
