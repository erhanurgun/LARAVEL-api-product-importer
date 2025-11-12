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
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('content')->nullable();

            $table->decimal('price', 12, 2);
            $table->decimal('old_price', 12, 2)->nullable();
            $table->unsignedTinyInteger('discount_percentage')->nullable();

            $table->unsignedInteger('quantity')->default(0);
            $table->boolean('in_stock')->default(true);

            $table->string('image_cover')->nullable();
            $table->string('image_thumbnail')->nullable();

            $table->string('container_type')->nullable();
            $table->string('container_size')->nullable();
            $table->unsignedSmallInteger('production_year')->nullable();
            $table->string('condition')->nullable();

            $table->string('location_city')->nullable();
            $table->string('location_district')->nullable();
            $table->string('location_country')->nullable();

            $table->string('type')->nullable();
            $table->boolean('is_new')->default(false);
            $table->boolean('is_hot_sale')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_bulk_sale')->default(false);
            $table->boolean('accept_offers')->default(false);
            $table->string('status')->default('draft');

            $table->json('colors')->nullable();
            $table->json('all_prices')->nullable();
            $table->json('technical_specs')->nullable();
            $table->json('user_info')->nullable();

            $table->timestamps();

            $table->index('slug');
            $table->index('status');
            $table->index(['in_stock', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
