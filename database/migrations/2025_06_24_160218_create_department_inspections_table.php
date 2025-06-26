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
        Schema::create('department_inspections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->foreign('patient_id')->references('id')->on('patients');
            
            $table->unsignedBigInteger('assigned_doctor_id')->nullable();
            $table->foreign('assigned_doctor_id')->references('id')->on('users');
            
            $table->unsignedBigInteger('medical_history_id');
            $table->foreign('medical_history_id')->references('id')->on('medical_histories');

            $table->text('complaints')->nullable();
            $table->text('medical_history')->nullable();
            $table->text('history_life')->nullable();
            $table->text('epidemiological_history')->nullable();
            $table->text('objectively')->nullable();
            $table->text('local_state')->nullable();
            $table->text('admission_diagnosis')->nullable();
            $table->text('recommended')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('department_inspections');
    }
};
