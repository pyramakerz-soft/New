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
        Schema::create('game_skills', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('skill_id')->nullable();
            $table->foreign(['skill_id'])->references(['id'])->on('skills')->onUpdate('no action')->onDelete('cascade');
            $table->unsignedBigInteger('game_type_id')->nullable();
            $table->foreign(['game_type_id'])->references(['id'])->on('game_types')->onUpdate('no action')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_skills');
    }
};
