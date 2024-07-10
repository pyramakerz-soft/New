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
        Schema::create('unit_endings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('warmup_id')->nullable()->unsigned();
            $table->foreign('warmup_id')->references('id')->on('warmups')->onDelete('cascade');
            $table->bigInteger('unit_id')->nullable()->unsigned();
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade');
            $table->bigInteger('test_id')->nullable()->unsigned();
            $table->foreign('test_id')->references('id')->on('tests')->onDelete('cascade');
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
        Schema::dropIfExists('unit_endings');
    }
};
