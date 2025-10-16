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
        Schema::create('menu_product_items', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('menu_id')->constrained()->cascadeOnDelete();
            // $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            // $table->double('quantity');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_product_items');
    }
};
