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
        Schema::create('lab_test_payment_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lab_test_history_id');
            $table->foreign('lab_test_history_id')->references('id')->on('lab_test_histories')->onDelete('cascade');

            $table->unsignedBigInteger('lab_test_payment_id');
            $table->foreign('lab_test_payment_id')->references('id')->on('lab_test_payments')->onDelete('cascade');

            $table->unsignedBigInteger('lab_test_id');
            $table->foreign('lab_test_id')->references('id')->on('lab_tests');
            
            $table->integer('sessions'); 
            $table->decimal('price', 10, 2);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_test_payment_details');
    }
};
