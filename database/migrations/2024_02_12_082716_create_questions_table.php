<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->integer('number')->nullable();
            $table->text('question')->nullable();
            $table->text('answer')->nullable();
            $table->integer('time')->nullable();
            $table->integer('difficulty')->nullable();
            $table->bigInteger('test_id')->nullable()->unsigned();
            $table->foreign('test_id')->references('id')->on('tests')->onDelete('cascade');
            
            $table->integer('type')->comment('0 => Complete , 1 => Choices , 2 => True/False');
            $table->text('first_part')->comment('First part of the complete question')->nullable();
            $table->text('second_part')->comment('Second part of the complete question')->nullable();
            $table->integer('true_flag')->comment('0 => False , 1 => True')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('questions');
    }
};