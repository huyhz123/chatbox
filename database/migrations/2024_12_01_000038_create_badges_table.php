<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('name_en', 100);
            $table->text('description')->nullable();
            $table->string('icon');
            $table->string('category', 50)->nullable(); // achievement, milestone, event, vip
            $table->json('requirements')->nullable(); // conditions to earn
            $table->integer('reward_coins')->default(0);
            $table->integer('reward_exp')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('category');
            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};
