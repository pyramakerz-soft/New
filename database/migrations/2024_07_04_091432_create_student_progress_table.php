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
        Schema::create('student_progress', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_id')->nullable()->index('student_progress_student_id_foreign');
            $table->unsignedBigInteger('program_id')->nullable()->index('student_progress_program_id_foreign');
            $table->unsignedBigInteger('unit_id')->nullable()->index('student_progress_unit_id_foreign');
            $table->unsignedBigInteger('lesson_id')->nullable()->index('student_progress_lesson_id_foreign');
            $table->unsignedBigInteger('question_id')->nullable()->index('student_progress_question_id_foreign');
            $table->boolean('is_done')->default(false);
            $table->timestamps();
            $table->integer('score')->nullable();
            $table->string('time')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->integer('mistake_count')->nullable();
            $table->unsignedBigInteger('test_id')->nullable()->index('student_progress_test_id_foreign');
            $table->string('stars', 75)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_progress');
    }
};
