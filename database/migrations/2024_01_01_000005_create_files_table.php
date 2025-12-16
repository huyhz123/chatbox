<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->longText('details')->nullable();
            $table->string('image')->nullable();
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->bigInteger('file_size')->default(0)->comment('File size in bytes');
            $table->string('version')->nullable();
            $table->decimal('price', 15, 2);
            $table->decimal('special_price', 15, 2)->nullable();
            $table->integer('download_limit')->default(5)->comment('Max downloads per purchase');
            $table->boolean('is_encrypted')->default(false);
            $table->string('preview_url')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sold_count')->default(0);
            $table->integer('download_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('file_downloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('ip_address')->nullable();
            $table->integer('download_count')->default(0);
            $table->timestamp('last_downloaded_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_downloads');
        Schema::dropIfExists('files');
    }
};
