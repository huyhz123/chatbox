<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->longText('details')->nullable();
            $table->string('image')->nullable();
            $table->decimal('price', 15, 2);
            $table->decimal('special_price', 15, 2)->nullable();
            $table->enum('level', ['beginner', 'intermediate', 'advanced'])->default('beginner');
            $table->integer('duration')->default(0)->comment('Duration in hours');
            $table->string('instructor_name')->nullable();
            $table->integer('access_days')->default(365)->comment('Days of access after purchase');
            $table->string('certificate')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('enrolled_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('course_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->longText('content')->nullable();
            $table->string('video_url')->nullable();
            $table->json('attachments')->nullable();
            $table->integer('duration')->default(0)->comment('Duration in minutes');
            $table->integer('order')->default(0);
            $table->boolean('is_free')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('course_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->timestamp('enrolled_at');
            $table->timestamp('expires_at')->nullable();
            $table->integer('progress')->default(0)->comment('Progress percentage');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('course_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('course_enrollments')->onDelete('cascade');
            $table->foreignId('lesson_id')->constrained('course_lessons')->onDelete('cascade');
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['enrollment_id', 'lesson_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_progress');
        Schema::dropIfExists('course_enrollments');
        Schema::dropIfExists('course_lessons');
        Schema::dropIfExists('courses');
    }
};
