<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('lesson_id')->nullable()->unsigned();
            $table->foreign('lesson_id')->references('id')->on('lessons')->onDelete('cascade');
            $table->bigInteger('game_type_id')->nullable()->unsigned();
            $table->foreign('game_type_id')->references('id')->on('game_types')->onDelete('cascade');
            $table->integer('audio_flag')->nullable()->comment('0 => No Audio ( Lower Difficulty ), 1 => Audio ( Higher Difficulty )');
            $table->integer('num_of_letters')->default('4');
            $table->integer('num_of_letter_repeat')->default('0');
            $table->integer('num_of_trials')->default('50');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('games');
    }
};
