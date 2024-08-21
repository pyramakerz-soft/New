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
        Schema::create('student_locks', function (Blueprint $table) {
            $table->id();
            $table->unSignedBigInteger('student_id')->nullable();
            $table->foreign(['student_id'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
            $table->unSignedBigInteger('unit_id')->nullable();
            $table->foreign(['unit_id'])->references(['id'])->on('units')->onUpdate('no action')->onDelete('cascade');
            $table->unSignedBigInteger('program_id')->nullable();
            $table->foreign(['program_id'])->references(['id'])->on('programs')->onUpdate('no action')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_locks');
    }
};
