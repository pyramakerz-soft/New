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
        Schema::create('benchmark_assign_tos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('number')->nullable();
            $table->unsignedBigInteger('benchmark_id')->nullable()->index('benchmark_assign_tos_benchmark_id_foreign');
            $table->unsignedBigInteger('unit_id')->nullable()->index('benchmark_assign_tos_unit_id_foreign');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('benchmark_assign_tos');
    }
};
