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
        Schema::create('lesson_endings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('lesson_id')->nullable()->index('lesson_endings_lesson_id_foreign');
            $table->unsignedBigInteger('test_id')->nullable()->index('lesson_endings_test_id_foreign');
            $table->unsignedBigInteger('homework_id')->nullable()->index('lesson_endings_homework_id_foreign');
            $table->text('video');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_endings');
    }
};
