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
        Schema::create('cashbox_sessions', function (Blueprint $table) {
            $table->id();
            $table->date('date'); // Har bir kun uchun bitta session
            $table->unsignedBigInteger('opened_by')->nullable();
            $table->foreign('opened_by')->references('id')->on('users');
            $table->unsignedBigInteger('closed_by')->nullable();
            $table->foreign('closed_by')->references('id')->on('users');
            $table->unsignedBigInteger('payment_type_id')->nullable();
            $table->foreign('payment_type_id')->references('id')->on('payment_types');
            
            $table->dateTime('opened_date'); // Har bir kun uchun bitta session
            $table->dateTime('closed_date')->nullable(); // Har bir kun uchun bitta session

            $table->decimal('opening_amount', 12, 2)->default(0); // Ochiqda qancha pul bor edi
            $table->decimal('closing_amount', 12, 2)->default(0); // Yopishda qancha pul bo‘ldi
            $table->timestamps();
            
            $table->unique(['date', 'payment_type_id'], 'unique_session_per_day_and_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashbox_sessions');
    }
};
