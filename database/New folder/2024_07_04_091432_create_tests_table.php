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
        Schema::create('tests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable();
            $table->integer('type')->comment('0 -> Quiz , 1 -> Homework');
            $table->timestamps();
            $table->unsignedBigInteger('lesson_id')->nullable()->index('tests_lesson_id_foreign');
            $table->unsignedBigInteger('user_id')->nullable()->index('tests_user_id_foreign');
            $table->unsignedBigInteger('owner_id')->nullable()->index('tests_owner_id_foreign');
            $table->unsignedBigInteger('program_id')->nullable()->index('tests_program_id_foreign');
            $table->double('degree')->nullable();
            $table->double('duration')->nullable();
            $table->integer('mistake_count')->nullable();
            $table->boolean('status')->nullable()->comment('0 => Inactive / 1 => Active');
            $table->string('difficulty_level', 11)->nullable();
            $table->text('image')->nullable();
            $table->unsignedBigInteger('stage_id')->nullable()->index('tests_stage_id_foreign');
            $table->unsignedBigInteger('game_id')->nullable()->index('tests_game_id_foreign');
            $table->boolean('isEdited')->default(false);
            $table->string('days_difference', 75)->nullable();
            $table->string('completed_at', 75)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tests');
    }
};
