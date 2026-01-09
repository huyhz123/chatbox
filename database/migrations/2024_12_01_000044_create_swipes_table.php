<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('swipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('chat_users')->onDelete('cascade');
            $table->foreignId('swiped_user_id')->constrained('chat_users')->onDelete('cascade');
            $table->enum('action', ['like', 'super_like', 'pass'])->default('like');
            $table->timestamps();

            $table->unique(['user_id', 'swiped_user_id']);
            $table->index('user_id');
            $table->index('swiped_user_id');
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('swipes');
    }
};
