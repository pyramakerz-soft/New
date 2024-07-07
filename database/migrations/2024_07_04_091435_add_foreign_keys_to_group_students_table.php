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
        Schema::table('group_students', function (Blueprint $table) {
            $table->foreign(['group_id'])->references(['id'])->on('groups')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['stage_id'])->references(['id'])->on('stages')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['student_id'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('group_students', function (Blueprint $table) {
            $table->dropForeign('group_students_group_id_foreign');
            $table->dropForeign('group_students_stage_id_foreign');
            $table->dropForeign('group_students_student_id_foreign');
        });
    }
};
