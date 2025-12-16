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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->morphs('reviewable'); // Product, Service, File, Course
            $table->integer('rating')->unsigned(); // 1-5 stars
            $table->string('title')->nullable();
            $table->text('comment')->nullable();
            $table->boolean('is_verified')->default(false); // Verified purchase
            $table->boolean('is_approved')->default(true);
            $table->integer('helpful_count')->default(0);
            $table->timestamps();

            $table->index(['reviewable_type', 'reviewable_id', 'is_approved']);
            $table->unique(['user_id', 'reviewable_type', 'reviewable_id'], 'user_review_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
