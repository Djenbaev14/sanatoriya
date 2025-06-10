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
            
            $table->unsignedBigInteger('medical_history_id');
            $table->foreign('medical_history_id')->references('id')->on('medical_histories');

            $table->decimal('amount', 10, 2);
            $table->enum('type', ['lab_test', 'procedure', 'daily_service']);
            $table->string('description')->nullable();
            $table->date('payment_date');
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
