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
        Schema::create('meal_preparations', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('menus_id')->constrained()->cascadeOnDelete();
            // $table->integer('people_count'); // necha odamga tayyorlanadi
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meal_preparations');
    }
};
