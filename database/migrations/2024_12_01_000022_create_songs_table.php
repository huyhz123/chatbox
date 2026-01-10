<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('songs', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->string('artist', 100)->nullable();
            $table->string('language', 5)->default('vi');
            $table->string('genre', 50)->nullable();
            $table->string('audio_url');
            $table->string('lyrics_url')->nullable();
            $table->string('instrumental_url')->nullable();
            $table->integer('duration'); // seconds
            $table->string('thumbnail')->nullable();
            $table->integer('plays_count')->default(0);
            $table->integer('favorites_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('language');
            $table->index('genre');
            $table->index('is_active');
            $table->index(['is_active', 'plays_count']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('songs');
    }
};
