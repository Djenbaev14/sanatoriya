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
        Schema::create('mkbs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mkb_class_id')->nullable();
            $table->foreign('mkb_class_id')->references('node_cd')->on('mkb_classes')->onDelete('cascade');
            $table->string('mkb_code')->nullable();
            $table->string('mkb_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mkbs');
    }
};
