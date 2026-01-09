<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chat_users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 50)->unique();
            $table->string('email', 100)->unique()->nullable();
            $table->string('phone', 20)->unique()->nullable();
            $table->string('password');
            $table->string('full_name', 100)->nullable();
            $table->string('avatar')->nullable();
            $table->string('cover_photo')->nullable();
            $table->text('bio')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->date('birthday')->nullable();
            $table->string('country_code', 5)->nullable();
            $table->string('language', 5)->default('vi');
            $table->integer('level')->default(1);
            $table->integer('exp')->default(0);
            $table->tinyInteger('vip_level')->default(0);
            $table->dateTime('vip_expire_at')->nullable();
            $table->decimal('balance', 15, 2)->default(0);
            $table->decimal('total_spent', 15, 2)->default(0);
            $table->string('status_text')->nullable();
            $table->enum('online_status', ['online', 'busy', 'away', 'invisible'])->default('online');
            $table->boolean('is_online')->default(false);
            $table->dateTime('last_seen')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_banned')->default(false);
            $table->string('remember_token', 100)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();

            $table->index('username');
            $table->index('country_code');
            $table->index('is_online');
            $table->index('vip_level');
            $table->index(['level', 'exp']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_users');
    }
};
