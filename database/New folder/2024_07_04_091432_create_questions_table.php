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
        Schema::create('questions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('number')->nullable();
            $table->text('question')->nullable();
            $table->text('answer')->nullable();
            $table->integer('time')->nullable();
            $table->integer('difficulty')->nullable();
            $table->unsignedBigInteger('test_id')->nullable()->index('questions_test_id_foreign');
            $table->text('type');
            $table->timestamps();
            $table->unsignedBigInteger('bank_id')->nullable()->index('questions_bank_id_foreign');
            $table->text('qtype')->nullable();
            $table->text('sec_type')->nullable();
            $table->boolean('show_num')->default(false);
            $table->longText('control')->nullable();
            $table->longText('choices')->nullable();
            $table->unsignedBigInteger('lesson_id')->nullable()->index('questions_lesson_id_foreign');
            $table->text('section_in_book');
            $table->string('control_audio')->nullable();
            $table->string('action_audio')->nullable();
            $table->longText('letters')->nullable();
            $table->longText('words')->nullable();
            $table->longText('images')->nullable();
            $table->string('story_video')->nullable();
            $table->string('program')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
