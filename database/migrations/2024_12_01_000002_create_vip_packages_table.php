<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vip_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('name_en', 50);
            $table->tinyInteger('level')->unique();
            $table->decimal('price', 10, 2);
            $table->integer('duration_days');
            $table->json('benefits')->nullable();
            $table->string('badge_image')->nullable();
            $table->string('frame_image')->nullable();
            $table->string('entrance_animation')->nullable();
            $table->string('color', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vip_packages');
    }
};
