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
            $table->id();
            $table->foreign('test_id')->references('id')->on('tests');
            $table->unsignedBigInteger('test_id')->nullable();
            $table->foreign('student_id')->references('id')->on('users');
            $table->unsignedBigInteger('student_id')->nullable();

            $table->float('final_degree')->nullable();
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
