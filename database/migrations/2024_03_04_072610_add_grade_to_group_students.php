<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('group_students', function (Blueprint $table) {
            $table->foreign('stage_id')->references('id')->on('stages');
            $table->unsignedBigInteger('stage_id')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('group_students', function (Blueprint $table) {
            //
        });
    }
};
