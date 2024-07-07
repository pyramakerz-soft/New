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
        Schema::table('checkpoint_assigned_tos', function (Blueprint $table) {
            $table->foreign(['checkpoint_id'])->references(['id'])->on('checkpoints')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['lesson_id'])->references(['id'])->on('lessons')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['program_id'])->references(['id'])->on('programs')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('checkpoint_assigned_tos', function (Blueprint $table) {
            $table->dropForeign('checkpoint_assigned_tos_checkpoint_id_foreign');
            $table->dropForeign('checkpoint_assigned_tos_lesson_id_foreign');
            $table->dropForeign('checkpoint_assigned_tos_program_id_foreign');
        });
    }
};
