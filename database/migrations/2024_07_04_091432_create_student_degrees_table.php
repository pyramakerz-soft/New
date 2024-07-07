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
        Schema::create('student_degrees', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('test_id')->nullable()->index('student_degrees_test_id_foreign');
            $table->unsignedBigInteger('student_id')->nullable()->index('student_degrees_student_id_foreign');
            $table->double('final_degree')->nullable();
            $table->unsignedBigInteger('game_id')->nullable()->index('student_degrees_game_id_foreign');
            $table->text('stars')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_degrees');
    }
};
