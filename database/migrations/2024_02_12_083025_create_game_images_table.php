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
        Schema::create('game_images', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('game_id')->nullable()->unsigned();
            $table->foreign('game_id')->references('id')->on('games')->onDelete('cascade');
            $table->bigInteger('game_letter_id')->nullable()->unsigned();
            $table->foreign('game_letter_id')->references('id')->on('game_letters')->onDelete('cascade');
            $table->integer('index')->nullable();
            $table->text('image')->nullable();
            $table->text('word')->nullable();
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
        Schema::dropIfExists('game_images');
    }
};
