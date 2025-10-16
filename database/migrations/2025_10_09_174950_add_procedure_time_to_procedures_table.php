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
        Schema::table('procedures', function (Blueprint $table) {
            $table->unsignedBigInteger('time_category_id')->nullable();
            $table->foreign('time_category_id')->references('id')->on('time_categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('procedures', function (Blueprint $table) {
            //
        });
    }
};
