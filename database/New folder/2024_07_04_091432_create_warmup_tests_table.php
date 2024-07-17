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
        Schema::create('warmup_tests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('warmup_id')->nullable()->index('warmup_tests_warmup_id_foreign');
            $table->unsignedBigInteger('test_id')->nullable()->index('warmup_tests_test_id_foreign');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warmup_tests');
    }
};
