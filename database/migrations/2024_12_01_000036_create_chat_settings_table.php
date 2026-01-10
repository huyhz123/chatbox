<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key_name', 100)->unique();
            $table->text('value')->nullable();
            $table->string('type', 20)->default('string'); // string, number, boolean, json
            $table->text('description')->nullable();
            $table->string('group', 50)->default('general');
            $table->timestamps();

            $table->index('key_name');
            $table->index('group');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_settings');
    }
};
