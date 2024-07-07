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
        Schema::table('benchmark_assign_tos', function (Blueprint $table) {
            $table->foreign(['benchmark_id'])->references(['id'])->on('benchmarks')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['unit_id'])->references(['id'])->on('units')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('benchmark_assign_tos', function (Blueprint $table) {
            $table->dropForeign('benchmark_assign_tos_benchmark_id_foreign');
            $table->dropForeign('benchmark_assign_tos_unit_id_foreign');
        });
    }
};
