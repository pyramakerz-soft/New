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
        Schema::create('beginnings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('program_id')->nullable()->index('beginnings_program_id_foreign');
            $table->unsignedBigInteger('test_id')->nullable()->index('beginnings_test_id_foreign');
            $table->text('video');
            $table->text('video_author');
            $table->text('video_message');
            $table->text('doc');
            $table->text('test')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beginnings');
    }
};
