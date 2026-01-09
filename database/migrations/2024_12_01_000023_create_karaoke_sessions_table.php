<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('karaoke_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('song_id')->constrained('songs');
            $table->foreignId('user_id')->constrained('chat_users')->onDelete('cascade');
            $table->string('recording_url')->nullable();
            $table->integer('score')->default(0);
            $table->enum('mode', ['solo', 'duet', 'battle'])->default('solo');
            $table->foreignId('partner_id')->nullable()->constrained('chat_users')->onDelete('set null');
            $table->integer('duration'); // seconds
            $table->integer('likes_count')->default(0);
            $table->integer('comments_count')->default(0);
            $table->boolean('is_public')->default(true);
            $table->timestamps();

            $table->index('user_id');
            $table->index('song_id');
            $table->index('mode');
            $table->index(['score', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('karaoke_sessions');
    }
};
