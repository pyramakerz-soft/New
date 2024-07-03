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
        Schema::create('endings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('program_id')->nullable()->unsigned();
            $table->foreign('program_id')->references('id')->on('programs')->onDelete('cascade');
            // $table->bigInteger('program_id')->nullable()->unsigned();
            // $table->foreign('program_id')->references('id')->on('programs')->onDelete('cascade');
            $table->bigInteger('test_id')->nullable()->unsigned();
            $table->foreign('test_id')->references('id')->on('tests')->onDelete('cascade');

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
        Schema::dropIfExists('endings');
    }
};
