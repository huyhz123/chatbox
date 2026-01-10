<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('chat_users')->onDelete('cascade');
            $table->enum('type', ['image', 'video', 'text'])->default('image');
            $table->string('media_url')->nullable();
            $table->text('text_content')->nullable();
            $table->string('background', 50)->nullable();
            $table->string('music')->nullable();
            $table->integer('duration')->default(5); // seconds
            $table->json('stickers')->nullable();
            $table->integer('views_count')->default(0);
            $table->dateTime('expires_at');
            $table->timestamps();

            $table->index('user_id');
            $table->index('expires_at');
            $table->index(['user_id', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stories');
    }
};
