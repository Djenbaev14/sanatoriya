<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procedure_details', function (Blueprint $table) {
            // Agar oldin foreign key bo‘lsa, avval uni o‘chirib tashlash kerak
            if (Schema::hasColumn('procedure_details', 'executor_id')) {
                $table->dropForeign(['executor_id']); // foreign keyni o‘chirish
                $table->dropColumn('executor_id');    // ustunni o‘chirish
            }
        });
    }

    public function down(): void
    {
        Schema::table('procedure_details', function (Blueprint $table) {
            // rollback uchun ustunni qayta tiklash
            $table->foreignId('executor_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }
};
