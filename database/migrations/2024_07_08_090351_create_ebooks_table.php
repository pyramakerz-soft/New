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
        Schema::create('ebooks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('url')->nullable();
            $table->string('file_path')->nullable();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('unit_id')->constrained()->onDelete('cascade');
            $table->enum('is_downloadable', ['true', 'false'])->default('false');
            $table->enum('file_type', ['pdf', 'word', 'ppt'])->nullable();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ebooks');
    }
};
