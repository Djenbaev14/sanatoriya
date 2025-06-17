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
        Schema::create('medical_inspections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id')->default(1);
            $table->foreign('patient_id')->references('id')->on('patients');
            
            $table->unsignedBigInteger('medical_history_id')->default(1);
            $table->foreign('medical_history_id')->references('id')->on('medical_histories');

            $table->unsignedBigInteger('status_payment_id')->default(1);
            $table->foreign('status_payment_id')->references('id')->on('status_payments');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_inspections');
    }
};
