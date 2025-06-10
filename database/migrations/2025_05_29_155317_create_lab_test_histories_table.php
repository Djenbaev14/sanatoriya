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
        Schema::create('lab_test_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('medical_history_id');
            $table->foreign('medical_history_id')->references('id')->on('medical_histories');
            
            $table->unsignedBigInteger('lab_test_id');
            $table->foreign('lab_test_id')->references('id')->on('lab_tests');
            
            // $table->date('test_date'); // Qachon tayinlandi yoki o‘tkazildi
            $table->string('result')->nullable(); // Tahlil natijasi
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->decimal('price', 10, 2); // O‘sha vaqtdagi narx (narx o‘zgarsa tarix saqlansin)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_test_histories');
    }
};
