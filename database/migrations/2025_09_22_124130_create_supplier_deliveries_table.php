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
        Schema::create('supplier_deliveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_id');
            $table->foreign('supplier_id')->references('id')->on('supplier_deliveries')->onDelete('cascade');
            $table->date('delivery_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_deliveries');
    }
};
