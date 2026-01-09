<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('chat_users')->onDelete('cascade');
            $table->enum('favorite_type', ['song', 'video', 'post', 'user'])->default('song');
            $table->unsignedBigInteger('favorite_id');
            $table->timestamps();

            $table->unique(['user_id', 'favorite_type', 'favorite_id']);
            $table->index('user_id');
            $table->index(['favorite_type', 'favorite_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_favorites');
    }
};
