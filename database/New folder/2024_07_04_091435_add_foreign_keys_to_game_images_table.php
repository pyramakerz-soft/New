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
        Schema::table('game_images', function (Blueprint $table) {
            $table->foreign(['game_id'])->references(['id'])->on('games')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['game_letter_id'])->references(['id'])->on('game_letters')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_images', function (Blueprint $table) {
            $table->dropForeign('game_images_game_id_foreign');
            $table->dropForeign('game_images_game_letter_id_foreign');
        });
    }
};
