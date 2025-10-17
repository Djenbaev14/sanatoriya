<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
    {
        Schema::table('procedure_sessions', function (Blueprint $table) {
            // executor_id ustunini qo‘shamiz
            $table->foreignId('executor_id')
                ->nullable()
                ->constrained('users') // users jadvaliga foreign key
                ->nullOnDelete()
                ->after('procedure_id'); // procedure_id dan keyin joylashadi
        });
    }

    public function down(): void
    {
        Schema::table('procedure_sessions', function (Blueprint $table) {
            // rollback uchun ustunni olib tashlaymiz
            $table->dropForeign(['executor_id']);
            $table->dropColumn('executor_id');
        });
    }
};
