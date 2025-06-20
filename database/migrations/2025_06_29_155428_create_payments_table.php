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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->foreign('patient_id')->references('id')->on('patients');
            
            $table->unsignedBigInteger('medical_history_id')->nullable();
            $table->foreign('medical_history_id')->references('id')->on('medical_histories');
            
            $table->unsignedBigInteger('assigned_procedure_id')->nullable();
            $table->foreign('assigned_procedure_id')->references('id')->on('assigned_procedures');
            
            $table->unsignedBigInteger('lab_test_history_id')->nullable();
            $table->foreign('lab_test_history_id')->references('id')->on('lab_test_histories');
            
            $table->unsignedBigInteger('medical_inspection_id')->nullable();
            $table->foreign('medical_inspection_id')->references('id')->on('medical_inspections');
            
            $table->unsignedBigInteger('payment_type_id');
            $table->foreign('payment_type_id')->references('id')->on('payment_types');

            $table->decimal('amount', 10, 2);
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
