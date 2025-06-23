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
        Schema::create('returned_procedure_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('returned_procedure_id');
            $table->foreign('returned_procedure_id')->references('id')->on('returned_procedures');
            
            $table->unsignedBigInteger('procedure_id');
            $table->foreign('procedure_id')->references('id')->on('procedures');
            
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
        Schema::dropIfExists('returned_procedure_details');
    }
};
