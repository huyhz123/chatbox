<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_privacy_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('chat_users')->onDelete('cascade');
            $table->enum('profile_visibility', ['public', 'friends', 'private'])->default('public');
            $table->enum('who_can_message', ['everyone', 'friends', 'no_one'])->default('everyone');
            $table->enum('who_can_call', ['everyone', 'friends', 'no_one'])->default('friends');
            $table->boolean('show_online_status')->default(true);
            $table->boolean('show_last_seen')->default(true);
            $table->boolean('show_read_receipts')->default(true);
            $table->boolean('allow_tags')->default(true);
            $table->boolean('allow_mentions')->default(true);
            $table->boolean('show_location')->default(false);
            $table->boolean('allow_friend_requests')->default(true);
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_privacy_settings');
    }
};
