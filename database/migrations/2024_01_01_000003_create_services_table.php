<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->longText('details')->nullable();
            $table->string('image')->nullable();
            $table->json('images')->nullable();
            $table->decimal('price', 15, 2);
            $table->decimal('cost', 15, 2)->default(0)->comment('Cost price for profit calculation');
            $table->decimal('special_price', 15, 2)->nullable();
            $table->enum('api_provider', ['dhru', 'gsm', 'manual'])->default('manual');
            $table->string('api_service_id')->nullable();
            $table->integer('min_quantity')->default(1);
            $table->integer('max_quantity')->default(1);
            $table->integer('processing_time')->default(0)->comment('Estimated time in minutes');
            $table->json('required_fields')->nullable()->comment('JSON array of required input fields');
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sold_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
