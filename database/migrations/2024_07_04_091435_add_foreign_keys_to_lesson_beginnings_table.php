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
        Schema::table('lesson_beginnings', function (Blueprint $table) {
            $table->foreign(['lesson_id'])->references(['id'])->on('lessons')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['test_id'])->references(['id'])->on('tests')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lesson_beginnings', function (Blueprint $table) {
            $table->dropForeign('lesson_beginnings_lesson_id_foreign');
            $table->dropForeign('lesson_beginnings_test_id_foreign');
        });
    }
};
