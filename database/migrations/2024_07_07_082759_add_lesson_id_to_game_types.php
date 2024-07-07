<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('game_types', function (Blueprint $table) {
            $table->unsignedBigInteger('lesson_id')->nullable()->index('games_lesson_id_foreign');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_types', function (Blueprint $table) {
            //
        });
    }
};
