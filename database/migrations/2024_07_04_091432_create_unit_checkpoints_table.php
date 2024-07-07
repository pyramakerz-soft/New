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
        Schema::create('unit_checkpoints', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('number')->nullable();
            $table->unsignedBigInteger('unit_id')->nullable()->index('unit_checkpoints_unit_id_foreign');
            $table->unsignedBigInteger('test_id')->nullable()->index('unit_checkpoints_test_id_foreign');
            $table->unsignedBigInteger('bank_id')->nullable()->index('unit_checkpoints_bank_id_foreign');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_checkpoints');
    }
};
