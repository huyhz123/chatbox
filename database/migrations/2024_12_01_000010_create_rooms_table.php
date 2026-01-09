<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('avatar')->nullable();
            $table->string('category', 50)->nullable();
            $table->string('country_code', 5)->nullable();
            $table->enum('type', ['public', 'private', 'vip'])->default('public');
            $table->string('password')->nullable();
            $table->integer('max_members')->default(100);
            $table->integer('max_seats')->default(12); // for voice rooms
            $table->foreignId('host_id')->nullable()->constrained('chat_users')->onDelete('set null');
            $table->json('settings')->nullable();
            $table->integer('member_count')->default(0);
            $table->integer('online_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('category');
            $table->index('type');
            $table->index('host_id');
            $table->index('country_code');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
