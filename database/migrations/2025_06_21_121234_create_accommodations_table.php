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
        Schema::create('accommodations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('main_accommodation_id')->nullable();
            $table->foreign('main_accommodation_id')->references('id')->on('accommodations');
            
            $table->unsignedBigInteger('patient_id');
            $table->foreign('patient_id')->references('id')->on('patients');
            
            $table->unsignedBigInteger('created_id')->nullable();
            $table->foreign('created_id')->references('id')->on('users');

            $table->unsignedBigInteger('main_patient_id')->nullable();
            $table->foreign('main_patient_id')->references('id')->on('patients');
            
            $table->unsignedBigInteger('medical_history_id')->nullable();
            $table->foreign('medical_history_id')->references('id')->on('medical_histories');

            $table->unsignedBigInteger('ward_id');
            $table->foreign('ward_id')->references('id')->on('wards');
            $table->unsignedBigInteger('bed_id');
            $table->foreign('bed_id')->references('id')->on('beds');
            $table->unsignedBigInteger('tariff_id');
            $table->foreign('tariff_id')->references('id')->on('tariffs');
            $table->decimal('tariff_price', 10, 2);
            
            $table->dateTime('admission_date')->nullable(); // Qabul qilingan sana
            $table->date('discharge_date')->nullable(); // Chiqish sanasi
            
            $table->unsignedBigInteger('meal_type_id');
            $table->foreign('meal_type_id')->references('id')->on('meal_types');
            $table->decimal('meal_price', 10, 2);
            
            $table->unsignedBigInteger('status_payment_id')->default(1);
            $table->foreign('status_payment_id')->references('id')->on('status_payments');
            
            $table->boolean('is_accomplice')->default(false); // True bo‘lsa — uxod qiluvchi
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accommodations');
    }
};
