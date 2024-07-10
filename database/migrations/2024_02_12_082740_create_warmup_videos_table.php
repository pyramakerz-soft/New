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
        Schema::create('warmup_videos', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('warmup_id')->nullable()->unsigned();
            $table->foreign('warmup_id')->references('id')->on('warmups')->onDelete('cascade');
            $table->text('video');
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
        Schema::dropIfExists('warmup_videos');
    }
};
