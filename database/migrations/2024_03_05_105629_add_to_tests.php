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
        Schema::table('tests', function (Blueprint $table) {
            $table->foreign('lesson_id')->references('id')->on('lessons');
            $table->unsignedBigInteger('lesson_id')->nullable();

            $table->foreign('user_id')->references('id')->on('users');
            $table->unsignedBigInteger('user_id')->nullable();

            $table->foreign('owner_id')->references('id')->on('users');
            $table->unsignedBigInteger('owner_id')->nullable();

            $table->foreign('program_id')->references('id')->on('programs');
            $table->unsignedBigInteger('program_id')->nullable();

            $table->float('degree')->nullable();
            $table->float('duration')->nullable();
            $table->integer('mistake_count')->nullable();
            $table->boolean('status')->nullable()->comment('0 => Inactive / 1 => Active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tests', function (Blueprint $table) {
            //
        });
    }
};
