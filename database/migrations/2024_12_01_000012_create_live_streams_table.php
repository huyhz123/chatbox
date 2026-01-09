<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_streams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('chat_users')->onDelete('cascade');
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('category', 50)->nullable();
            $table->string('stream_key', 100)->unique();
            $table->string('rtmp_url')->nullable();
            $table->string('hls_url')->nullable();
            $table->string('agora_channel_id')->nullable();
            $table->integer('viewer_count')->default(0);
            $table->integer('peak_viewers')->default(0);
            $table->decimal('total_gifts_value', 15, 2)->default(0);
            $table->integer('likes_count')->default(0);
            $table->integer('comments_count')->default(0);
            $table->enum('status', ['scheduled', 'live', 'ended'])->default('scheduled');
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->integer('duration_seconds')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
            $table->index('category');
            $table->index(['status', 'viewer_count']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_streams');
    }
};
