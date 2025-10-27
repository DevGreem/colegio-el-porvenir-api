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
        Schema::create('class_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->jsonb('monday')->default('[]');
            $table->jsonb('tuesday')->default('[]');
            $table->jsonb('wednesday')->default('[]');
            $table->jsonb('thursday')->default('[]');
            $table->jsonb('friday')->default('[]');
            $table->timestamps();

            $table->unique('course_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_schedules');
    }
};
