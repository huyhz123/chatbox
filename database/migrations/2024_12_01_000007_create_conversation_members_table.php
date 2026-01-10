<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversation_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('chat_users')->onDelete('cascade');
            $table->enum('role', ['owner', 'admin', 'moderator', 'member'])->default('member');
            $table->boolean('is_muted')->default(false);
            $table->dateTime('last_read_at')->nullable();
            $table->timestamp('joined_at')->useCurrent();

            $table->unique(['conversation_id', 'user_id']);
            $table->index('user_id');
            $table->index('conversation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation_members');
    }
};
