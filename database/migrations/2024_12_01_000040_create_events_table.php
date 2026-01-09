<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->string('title_en', 200);
            $table->text('description')->nullable();
            $table->string('banner_image')->nullable();
            $table->enum('type', ['spending', 'coin_bonus', 'gift', 'tournament', 'holiday', 'special'])->default('special');
            $table->json('rewards')->nullable();
            $table->json('conditions')->nullable();
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('type');
            $table->index(['is_active', 'start_at', 'end_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
