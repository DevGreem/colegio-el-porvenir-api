<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(<<<'SQL'
            CREATE OR REPLACE VIEW student_course_view AS
            SELECT
                s.id AS student_id,
                s.user_id,
                s.document,
                s.first_name,
                s.last_name,
                s.birthdate,
                s.gender,
                s.enrollment_date,
                s.enrollment_status,
                s.phone,
                s.course_id,
                c.grade_level,
                c.section,
                s.created_at,
                s.updated_at
            FROM students s
            LEFT JOIN courses c ON c.id = s.course_id
        SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS student_course_view');
    }
};
