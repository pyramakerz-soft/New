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
        Schema::create('checkpoint_assigned_tos', function (Blueprint $table) {
            $table->id();
            $table->integer('number')->nullable();
            $table->bigInteger('checkpoint_id')->unsigned()->nullable();
            $table->bigInteger('program_id')->unsigned()->nullable();
            $table->bigInteger('lesson_id')->unsigned()->nullable();
            $table->foreign('program_id')->references('id')->on('programs')->onDelete('cascade');
            $table->foreign('checkpoint_id')->references('id')->on('checkpoints')->onDelete('cascade');
            $table->foreign('lesson_id')->references('id')->on('lessons')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkpoint_assigned_tos');
    }
};
