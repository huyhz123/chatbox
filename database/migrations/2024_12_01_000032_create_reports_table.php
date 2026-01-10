<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('chat_users')->onDelete('cascade');
            $table->foreignId('reported_user_id')->nullable()->constrained('chat_users')->onDelete('cascade');
            $table->enum('content_type', ['user', 'post', 'message', 'video', 'stream', 'comment'])->nullable();
            $table->unsignedBigInteger('content_id')->nullable();
            $table->string('reason', 100);
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'reviewing', 'resolved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('chat_users')->onDelete('set null');
            $table->dateTime('reviewed_at')->nullable();
            $table->string('action_taken')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->index('reporter_id');
            $table->index('reported_user_id');
            $table->index(['content_type', 'content_id']);
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
