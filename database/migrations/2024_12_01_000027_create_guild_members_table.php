<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guild_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guild_id')->constrained('guilds')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('chat_users')->onDelete('cascade');
            $table->enum('role', ['leader', 'vice_leader', 'elite', 'member'])->default('member');
            $table->integer('contribution_points')->default(0);
            $table->timestamp('joined_at')->useCurrent();

            $table->unique(['guild_id', 'user_id']);
            $table->index('user_id');
            $table->index('guild_id');
            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guild_members');
    }
};
