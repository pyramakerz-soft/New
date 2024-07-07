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
        Schema::table('game_letters', function (Blueprint $table) {
            $table->foreign(['game_id'])->references(['id'])->on('games')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_letters', function (Blueprint $table) {
            $table->dropForeign('game_letters_game_id_foreign');
        });
    }
};
