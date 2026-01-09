<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guilds', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('logo')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('leader_id')->nullable()->constrained('chat_users')->onDelete('set null');
            $table->integer('level')->default(1);
            $table->integer('exp')->default(0);
            $table->integer('max_members')->default(50);
            $table->integer('member_count')->default(0);
            $table->json('requirements')->nullable(); // min_level, min_vip, etc.
            $table->json('settings')->nullable();
            $table->decimal('total_contribution', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('name');
            $table->index('leader_id');
            $table->index(['level', 'exp']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guilds');
    }
};
