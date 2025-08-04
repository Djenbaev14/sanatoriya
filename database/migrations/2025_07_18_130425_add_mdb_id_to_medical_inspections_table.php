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
        Schema::table('medical_inspections', function (Blueprint $table) {
            $table->unsignedBigInteger('mkb_class_id')->nullable();
            $table->foreign('mkb_class_id')->references('id')->on('mkb_classes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medical_inspections', function (Blueprint $table) {
            //
        });
    }
};
