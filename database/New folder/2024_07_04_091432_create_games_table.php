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
        Schema::create('games', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 250)->nullable();
            $table->unsignedBigInteger('lesson_id')->nullable()->index('games_lesson_id_foreign');
            $table->text('inst')->nullable();
            $table->unsignedBigInteger('game_type_id')->nullable()->index('games_game_type_id_foreign');
            $table->integer('audio_flag')->nullable()->comment('0 => No Audio ( Lower Difficulty ), 1 => Audio ( Higher Difficulty )');
            $table->integer('num_of_letters')->default(4);
            $table->integer('num_of_letter_repeat')->default(0);
            $table->integer('num_of_trials')->default(50);
            $table->timestamps();
            $table->string('main_letter', 10)->nullable();
            $table->integer('stars')->nullable();
            $table->unsignedBigInteger('prev_game_id')->nullable()->index('games_prev_game_id_foreign');
            $table->unsignedBigInteger('next_game_id')->nullable()->index('games_next_game_id_foreign');
            $table->text('correct_ans')->nullable();
            $table->boolean('is_edited')->default(false);
            $table->text('video')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
