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
        Schema::table('checkpoints', function (Blueprint $table) {
            $table->foreign(['bank_id'])->references(['id'])->on('question_banks')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['test_id'])->references(['id'])->on('tests')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('checkpoints', function (Blueprint $table) {
            $table->dropForeign('checkpoints_bank_id_foreign');
            $table->dropForeign('checkpoints_test_id_foreign');
        });
    }
};
