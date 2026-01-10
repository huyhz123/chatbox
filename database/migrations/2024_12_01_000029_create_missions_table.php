<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('missions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('name_en', 100);
            $table->text('description')->nullable();
            $table->enum('type', ['daily', 'weekly', 'monthly', 'event'])->default('daily');
            $table->string('category', 50)->nullable(); // login, message, gift, room, etc.
            $table->integer('target_value'); // e.g., send 50 messages
            $table->integer('reward_coins')->default(0);
            $table->integer('reward_exp')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('type');
            $table->index('category');
            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('missions');
    }
};
