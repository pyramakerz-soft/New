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
        Schema::create('warmup_videos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('warmup_id')->nullable()->index('warmup_videos_warmup_id_foreign');
            $table->text('video');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warmup_videos');
    }
};
