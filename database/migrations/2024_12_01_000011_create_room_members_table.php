<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('chat_users')->onDelete('cascade');
            $table->enum('role', ['host', 'moderator', 'member'])->default('member');
            $table->tinyInteger('seat_number')->nullable(); // for voice rooms
            $table->boolean('is_muted')->default(false);
            $table->boolean('is_speaking')->default(false);
            $table->timestamp('joined_at')->useCurrent();

            $table->unique(['room_id', 'user_id']);
            $table->index('seat_number');
            $table->index('room_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_members');
    }
};
