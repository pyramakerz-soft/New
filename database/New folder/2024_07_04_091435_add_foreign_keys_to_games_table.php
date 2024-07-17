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
        Schema::table('games', function (Blueprint $table) {
            $table->foreign(['game_type_id'])->references(['id'])->on('game_types')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['lesson_id'])->references(['id'])->on('lessons')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['next_game_id'])->references(['id'])->on('games')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['prev_game_id'])->references(['id'])->on('games')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropForeign('games_game_type_id_foreign');
            $table->dropForeign('games_lesson_id_foreign');
            $table->dropForeign('games_next_game_id_foreign');
            $table->dropForeign('games_prev_game_id_foreign');
        });
    }
};
