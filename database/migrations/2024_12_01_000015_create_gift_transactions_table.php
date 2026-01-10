<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gift_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('chat_users')->onDelete('cascade');
            $table->foreignId('receiver_id')->constrained('chat_users')->onDelete('cascade');
            $table->foreignId('gift_id')->constrained('gifts');
            $table->integer('quantity')->default(1);
            $table->integer('total_price'); // in coins
            $table->enum('context_type', ['chat', 'livestream', 'post', 'room', 'profile'])->nullable();
            $table->unsignedBigInteger('context_id')->nullable();
            $table->boolean('is_public')->default(true);
            $table->string('message')->nullable();
            $table->timestamps();

            $table->index('sender_id');
            $table->index('receiver_id');
            $table->index('gift_id');
            $table->index(['context_type', 'context_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gift_transactions');
    }
};
