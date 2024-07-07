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
        Schema::create('student_tests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_id')->nullable()->index('student_tests_student_id_foreign');
            $table->unsignedBigInteger('group_id')->nullable()->index('student_tests_group_id_foreign');
            $table->unsignedBigInteger('test_id')->nullable()->index('student_tests_test_id_foreign');
            $table->unsignedBigInteger('lesson_id')->nullable()->index('student_tests_lesson_id_foreign');
            $table->timestamps();
            $table->unsignedBigInteger('program_id')->nullable()->index('student_tests_program_id_foreign');
            $table->unsignedBigInteger('teacher_id')->nullable()->index('student_tests_teacher_id_foreign');
            $table->date('due_date')->nullable();
            $table->dateTime('start_date')->useCurrent();
            $table->string('status')->nullable();
            $table->string('status_enum')->nullable();
            $table->text('image')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_tests');
    }
};
