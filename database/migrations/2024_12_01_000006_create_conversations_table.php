<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['private', 'group', 'room'])->default('private');
            $table->string('name', 100)->nullable();
            $table->string('avatar')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('chat_users')->onDelete('set null');
            $table->json('settings')->nullable();
            $table->dateTime('last_message_at')->nullable();
            $table->timestamps();

            $table->index('type');
            $table->index('created_by');
            $table->index('last_message_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
