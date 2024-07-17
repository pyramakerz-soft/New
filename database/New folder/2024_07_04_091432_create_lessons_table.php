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
        Schema::create('lessons', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('name');
            $table->integer('number');
            $table->unsignedBigInteger('warmup_id')->nullable()->index('lessons_warmup_id_foreign');
            $table->unsignedBigInteger('unit_id')->nullable()->index('lessons_unit_id_foreign');
            $table->timestamps();
            $table->integer('stars')->nullable();
            $table->text('main_letter')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
