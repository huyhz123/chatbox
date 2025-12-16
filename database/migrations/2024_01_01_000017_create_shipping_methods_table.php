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
        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->decimal('base_cost', 10, 2)->default(0);
            $table->string('calculation_type')->default('fixed'); // fixed, weight_based, distance_based
            $table->json('pricing_rules')->nullable();
            $table->integer('estimated_days_min')->nullable();
            $table->integer('estimated_days_max')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Add shipping to orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('shipping_method_id')->nullable()->after('currency')->constrained()->nullOnDelete();
            $table->decimal('shipping_cost', 10, 2)->default(0)->after('total_amount');
            $table->string('tracking_number')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['shipping_method_id']);
            $table->dropColumn(['shipping_method_id', 'shipping_cost', 'tracking_number']);
        });

        Schema::dropIfExists('shipping_methods');
    }
};
