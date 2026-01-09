<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('chat_users')->onDelete('cascade');
            $table->foreignId('badge_id')->constrained('badges')->onDelete('cascade');
            $table->boolean('is_equipped')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'badge_id']);
            $table->index('user_id');
            $table->index('is_equipped');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_badges');
    }
};
