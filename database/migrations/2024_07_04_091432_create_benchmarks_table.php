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
        Schema::create('benchmarks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('number');
            $table->unsignedBigInteger('program_id')->nullable()->index('benchmarks_program_id_foreign');
            $table->unsignedBigInteger('test_id')->nullable()->index('benchmarks_test_id_foreign');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('benchmarks');
    }
};
