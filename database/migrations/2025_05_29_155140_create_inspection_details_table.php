<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspection_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('medical_inspection_id');
            $table->foreign('medical_inspection_id')->references('id')->on('medical_inspections');
            
            $table->unsignedBigInteger('inspection_id');
            $table->foreign('inspection_id')->references('id')->on('inspections');
            
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
        Schema::dropIfExists('inspection_details');
    }
};
