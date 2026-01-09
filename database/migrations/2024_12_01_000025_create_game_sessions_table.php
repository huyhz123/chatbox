<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('games');
            $table->foreignId('host_id')->constrained('chat_users')->onDelete('cascade');
            $table->enum('status', ['waiting', 'playing', 'finished'])->default('waiting');
            $table->json('players')->nullable(); // array of player_ids with scores
            $table->integer('bet_amount')->default(0); // in coins
            $table->foreignId('winner_id')->nullable()->constrained('chat_users')->onDelete('set null');
            $table->json('game_data')->nullable(); // game state, moves, etc.
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->timestamps();

            $table->index('game_id');
            $table->index('host_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_sessions');
    }
};
