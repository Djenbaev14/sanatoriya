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
            $table->date('date')->unique(); // Har bir kun uchun bitta session
            $table->unsignedBigInteger('opened_by');
            $table->foreign('opened_by')->references('id')->on('users');
            $table->unsignedBigInteger('closed_by');
            $table->foreign('closed_by')->references('id')->on('users');

            $table->decimal('opening_amount', 12, 2)->default(0); // Ochiqda qancha pul bor edi
            $table->decimal('closing_amount', 12, 2)->nullable(); // Yopishda qancha pul bo‘ldi
            $table->timestamps();
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
