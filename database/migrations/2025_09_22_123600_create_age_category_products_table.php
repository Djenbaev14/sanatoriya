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
        Schema::create('age_category_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->unsignedBigInteger('age_category_id');
            $table->foreign('age_category_id')->references('id')->on('age_categories')->onDelete('cascade');
            $table->double('quantity');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('age_category_products');
    }
};
