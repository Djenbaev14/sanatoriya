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
        Schema::create('accommodation_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('accommodation_id')->nullable();
            $table->foreign('accommodation_id')->references('id')->on('accommodations')->ondelete('cascade');
            
            $table->unsignedBigInteger('payment_id');
            $table->foreign('payment_id')->references('id')->on('payments')->onDelete('cascade');
            
            $table->unsignedBigInteger('medical_history_id')->nullable();
            $table->foreign('medical_history_id')->references('id')->on('medical_histories')->onDelete('cascade');

            $table->decimal('tariff_price', 10, 2);
            $table->decimal('meal_price', 10, 2);
            
            $table->integer('ward_day')->nullable(); 
            $table->integer('meal_day')->nullable(); 
            
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accommodation_payments');
    }
};
