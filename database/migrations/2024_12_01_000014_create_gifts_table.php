<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gifts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('name_en', 50);
            $table->string('image');
            $table->string('animation_url')->nullable();
            $table->string('animation_type', 20)->default('2d'); // 2d, 3d, lottie
            $table->integer('price'); // in coins
            $table->enum('category', ['free', 'basic', 'special', 'vip', 'lucky'])->default('basic');
            $table->integer('duration_ms')->default(2000); // animation duration
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('category');
            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gifts');
    }
};
