<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('couples', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user1_id')->constrained('chat_users')->onDelete('cascade');
            $table->foreignId('user2_id')->constrained('chat_users')->onDelete('cascade');
            $table->enum('status', ['pending', 'active', 'broken'])->default('pending');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('broken_at')->nullable();
            $table->integer('days_together')->default(0);
            $table->timestamps();

            $table->index(['user1_id', 'user2_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('couples');
    }
};
