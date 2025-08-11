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
        Schema::create('lab_test_mkbs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lab_test_id')->nullable();
            $table->foreign('lab_test_id')->references('id')->on('lab_tests');
            
            $table->unsignedBigInteger('mkb_class_id')->nullable();
            $table->foreign('mkb_class_id')->references('id')->on('mkb_classes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_test_mkbs');
    }
};
