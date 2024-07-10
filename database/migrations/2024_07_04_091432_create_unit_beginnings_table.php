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
        Schema::create('unit_beginnings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('unit_id')->nullable()->index('unit_beginnings_unit_id_foreign');
            $table->unsignedBigInteger('test_id')->nullable()->index('unit_beginnings_test_id_foreign');
            $table->text('video')->nullable();
            $table->text('video_author');
            $table->text('video_message');
            $table->text('doc')->nullable();
            $table->text('test')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_beginnings');
    }
};
