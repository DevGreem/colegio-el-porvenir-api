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
            CREATE OR REPLACE VIEW user_student_view AS
            SELECT
                u.id AS user_id,
                u.name AS user_name,
                u.email,
                u.user_type,
                u.email_verified_at,
                u.created_at AS user_created_at,
                u.updated_at AS user_updated_at,
                scv.student_id,
                scv.document,
                scv.first_name,
                scv.last_name,
                scv.birthdate,
                scv.gender,
                scv.enrollment_date,
                scv.enrollment_status,
                scv.phone,
                scv.course_id,
                scv.grade_level,
                scv.section,
                scv.created_at AS student_created_at,
                scv.updated_at AS student_updated_at
            FROM users u
            LEFT JOIN student_course_view scv ON scv.user_id = u.id
        SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS user_student_view');
    }
};
