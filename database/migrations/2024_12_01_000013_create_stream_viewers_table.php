<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stream_viewers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stream_id')->constrained('live_streams')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('chat_users')->onDelete('cascade');
            $table->dateTime('joined_at');
            $table->dateTime('left_at')->nullable();
            $table->integer('watch_duration_seconds')->default(0);

            $table->index('stream_id');
            $table->index('user_id');
            $table->index(['stream_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stream_viewers');
    }
};
