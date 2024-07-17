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
        Schema::table('endings', function (Blueprint $table) {
            $table->foreign(['program_id'])->references(['id'])->on('programs')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['test_id'])->references(['id'])->on('tests')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('endings', function (Blueprint $table) {
            $table->dropForeign('endings_program_id_foreign');
            $table->dropForeign('endings_test_id_foreign');
        });
    }
};
