<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_missions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('chat_users')->onDelete('cascade');
            $table->foreignId('mission_id')->constrained('missions');
            $table->integer('current_value')->default(0);
            $table->boolean('is_completed')->default(false);
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('claimed_at')->nullable();
            $table->date('reset_date')->nullable(); // for daily/weekly missions
            $table->timestamps();

            $table->index('user_id');
            $table->index('mission_id');
            $table->index(['user_id', 'is_completed']);
            $table->index('reset_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_missions');
    }
};
