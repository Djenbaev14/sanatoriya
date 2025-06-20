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
        Schema::create('medical_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->foreign('patient_id')->references('id')->on('patients');
            $table->unsignedBigInteger('doctor_id');
            $table->foreign('doctor_id')->references('id')->on('users');
            
            $table->dateTime('admission_date')->nullable(); // Qabul qilingan sana
            $table->date('discharge_date')->nullable(); // Chiqish sanasi
            
            $table->unsignedBigInteger('bed_meal_status_payment_id')->default(1);
            $table->foreign('bed_meal_status_payment_id')->references('id')->on('status_payments');
            
            $table->string('height')->nullable();
            $table->string('weight')->nullable();
            $table->string('temperature')->nullable();
            $table->json('disability_types')->nullable();

            $table->text('side_effects')->nullable();
            $table->boolean('is_emergency')->default(false);
            $table->enum('transport_type', ['ambulance', 'family', 'self', 'taxi', 'other'])->nullable();
            $table->enum('referred_from', [
                'clinic',
                'hospital',
                'emergency',
                'self',
                'other',
            ])->nullable();
            $table->string('photo')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_histories');
    }
};
