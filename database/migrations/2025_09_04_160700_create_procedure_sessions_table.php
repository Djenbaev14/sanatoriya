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
        Schema::create('procedure_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assigned_procedure_id');
            $table->foreign('assigned_procedure_id')->references('id')->on('assigned_procedures')->onDelete('cascade');
            
            $table->unsignedBigInteger('procedure_detail_id');
            $table->foreign('procedure_detail_id')->references('id')->on('procedure_details')->onDelete('cascade');
            
            $table->unsignedBigInteger('procedure_id');
            $table->foreign('procedure_id')->references('id')->on('procedures')->onDelete('cascade');
            
            $table->unsignedBigInteger('executor_id')->nullable();
            $table->foreign('executor_id')->references('id')->on('users');

            $table->date('session_date'); // sanani ISO formatda saqlaymiz: YYYY-MM-DD
            $table->boolean('is_completed')->default(false); // seans bajarildimi yoki yo‘q
            // bajarilgan sana
            $table->date('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procedure_sessions');
    }
};
