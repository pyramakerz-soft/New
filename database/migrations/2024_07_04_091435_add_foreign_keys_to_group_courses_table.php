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
        Schema::table('group_courses', function (Blueprint $table) {
            $table->foreign(['group_id'])->references(['id'])->on('groups')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['program_id'])->references(['id'])->on('programs')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('group_courses', function (Blueprint $table) {
            $table->dropForeign('group_courses_group_id_foreign');
            $table->dropForeign('group_courses_program_id_foreign');
        });
    }
};
