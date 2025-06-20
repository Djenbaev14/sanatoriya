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
        Schema::create('medical_beds', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('medical_history_id');
            $table->foreign('medical_history_id')->references('id')->on('medical_histories');

            $table->unsignedBigInteger('ward_id');
            $table->foreign('ward_id')->references('id')->on('wards');
            
            $table->unsignedBigInteger('bed_id');
            $table->foreign('bed_id')->references('id')->on('beds');

            $table->unsignedBigInteger('tariff_id');
            $table->foreign('tariff_id')->references('id')->on('tariffs');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_beds');
    }
};
