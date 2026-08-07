<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medical_histories', function (Blueprint $table) {
            $table->string('type')->default('paid')->after('patient_id'); // paid, disabled, outpatient
        });

        // Agar 'number' hozir integer bolsa, string qilib ozgartiramiz (N-1, O-1 saqlash uchun)
        Schema::table('medical_histories', function (Blueprint $table) {
            $table->string('number')->change();
        });
    }

    public function down(): void
    {
        Schema::table('medical_histories', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
