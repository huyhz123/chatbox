<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rankings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('chat_users')->onDelete('cascade');
            $table->enum('rank_type', ['vip', 'gifter', 'receiver', 'level', 'streamer', 'singer', 'gamer', 'guild'])->default('level');
            $table->enum('period', ['daily', 'weekly', 'monthly', 'all_time'])->default('all_time');
            $table->decimal('points', 15, 2)->default(0);
            $table->integer('rank_position')->default(0);
            $table->string('country_code', 5)->nullable();
            $table->timestamp('updated_at')->useCurrent();

            $table->unique(['user_id', 'rank_type', 'period']);
            $table->index(['rank_type', 'period', 'rank_position']);
            $table->index(['rank_type', 'period', 'country_code', 'rank_position']);
            $table->index('updated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rankings');
    }
};
