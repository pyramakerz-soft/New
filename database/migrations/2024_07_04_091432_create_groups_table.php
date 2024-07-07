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
        Schema::create('groups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('name');
            $table->text('sec_name')->nullable();
            $table->unsignedBigInteger('school_id')->nullable()->index('groups_school_id_foreign');
            $table->timestamps();
            $table->unsignedBigInteger('stage_id')->nullable()->index('groups_stage_id_foreign');
            $table->unsignedBigInteger('program_id')->nullable()->index('groups_program_id_foreign');
            $table->unsignedBigInteger('teacher_id')->nullable()->index('groups_teacher_id_foreign');
            $table->text('image')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
