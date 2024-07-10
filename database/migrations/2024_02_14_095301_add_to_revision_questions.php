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
        Schema::table('revision_questions_banks', function (Blueprint $table) {
            $table->integer('time')->nullable();
            $table->integer('difficulty')->nullable();
            $table->string('choices')->nullable();
            $table->integer('type')->comment('0 => Complete , 1 => Choices , 2 => True/False');
            $table->text('first_part')->comment('First part of the complete question')->nullable();
            $table->text('second_part')->comment('Second part of the complete question')->nullable();
            $table->integer('true_flag')->comment('0 => False , 1 => True')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('revision_questions_banks', function (Blueprint $table) {
            //
        });
    }
};
