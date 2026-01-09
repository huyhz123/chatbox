<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dating_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('chat_users')->onDelete('cascade');
            $table->json('photos')->nullable(); // max 6 photos
            $table->text('bio')->nullable();
            $table->json('interests')->nullable();
            $table->enum('looking_for', ['friendship', 'dating', 'relationship', 'networking'])->default('dating');
            $table->integer('height')->nullable(); // in cm
            $table->string('work', 100)->nullable();
            $table->string('education', 100)->nullable();
            $table->integer('age_min')->default(18);
            $table->integer('age_max')->default(50);
            $table->integer('distance_radius')->default(50); // km
            $table->enum('preferred_gender', ['male', 'female', 'both'])->default('both');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('user_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dating_profiles');
    }
};
