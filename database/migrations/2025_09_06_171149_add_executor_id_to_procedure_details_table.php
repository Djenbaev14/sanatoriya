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
        Schema::table('procedure_details', function (Blueprint $table) {
            $table->unsignedBigInteger('executor_id')->nullable()->after('procedure_id');
            $table->foreign('executor_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('procedure_details', function (Blueprint $table) {
            //
        });
    }
};
