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
        Schema::create('game_images', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('game_id')->nullable()->index('game_images_game_id_foreign');
            $table->unsignedBigInteger('game_letter_id')->nullable()->index('game_images_game_letter_id_foreign');
            $table->integer('index')->nullable();
            $table->text('image')->nullable();
            $table->text('word');
            $table->timestamps();
            $table->tinyInteger('correct')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_images');
    }
};
