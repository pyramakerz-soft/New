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
        Schema::table('groups', function (Blueprint $table) {
            $table->foreign(['program_id'])->references(['id'])->on('programs')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['school_id'])->references(['id'])->on('schools')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['stage_id'])->references(['id'])->on('stages')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['teacher_id'])->references(['id'])->on('users')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropForeign('groups_program_id_foreign');
            $table->dropForeign('groups_school_id_foreign');
            $table->dropForeign('groups_stage_id_foreign');
            $table->dropForeign('groups_teacher_id_foreign');
        });
    }
};
