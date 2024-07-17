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
        Schema::create('unit_endings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('warmup_id')->nullable()->index('unit_endings_warmup_id_foreign');
            $table->unsignedBigInteger('unit_id')->nullable()->index('unit_endings_unit_id_foreign');
            $table->unsignedBigInteger('test_id')->nullable()->index('unit_endings_test_id_foreign');
            $table->unsignedBigInteger('bank_id')->nullable()->index('unit_endings_bank_id_foreign');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_endings');
    }
};
