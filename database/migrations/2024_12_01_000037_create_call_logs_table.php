<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('call_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caller_id')->constrained('chat_users')->onDelete('cascade');
            $table->foreignId('receiver_id')->nullable()->constrained('chat_users')->onDelete('cascade');
            $table->enum('type', ['audio', 'video', 'random'])->default('audio');
            $table->enum('status', ['ringing', 'answered', 'missed', 'declined', 'failed'])->default('ringing');
            $table->integer('duration_seconds')->default(0);
            $table->string('agora_channel_id')->nullable();
            $table->json('participants')->nullable(); // for group calls
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->timestamps();

            $table->index('caller_id');
            $table->index('receiver_id');
            $table->index('type');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('call_logs');
    }
};
