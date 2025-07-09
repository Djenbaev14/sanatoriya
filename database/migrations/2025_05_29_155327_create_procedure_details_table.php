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
        Schema::create('procedure_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assigned_procedure_id');
            $table->foreign('assigned_procedure_id')->references('id')->on('assigned_procedures')->onDelete('cascade');
            
            $table->unsignedBigInteger('procedure_id');
            $table->foreign('procedure_id')->references('id')->on('procedures');
            
            $table->integer('sessions'); // ✅ nechta marta/protsedura oladi
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
        Schema::dropIfExists('procedure_details');
    }
};
