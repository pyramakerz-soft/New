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
        Schema::create('revision_questions_banks', function (Blueprint $table) {
            $table->id();
            $table->integer('number');
            $table->text('question');
            $table->text('answer')->nullable();
            $table->bigInteger('bank_id')->nullable()->unsigned();
            $table->foreign('bank_id')->references('id')->on('question_banks')->onDelete('cascade');

            




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
        Schema::dropIfExists('revision_questions_banks');
    }
};
