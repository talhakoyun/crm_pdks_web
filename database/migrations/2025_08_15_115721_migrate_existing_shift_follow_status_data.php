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
        // Yeni alanları ekle (sadece yoksa)
        Schema::table('shift_follows', function (Blueprint $table) {
            if (!Schema::hasColumn('shift_follows', 'status')) {
                $table->enum('status', ['normal', 'late', 'early_out'])->default('normal')->after('is_active');
            }
            if (!Schema::hasColumn('shift_follows', 'status_minutes')) {
                $table->integer('status_minutes')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Yeni alanları kaldır (sadece varsa)
        Schema::table('shift_follows', function (Blueprint $table) {
            if (Schema::hasColumn('shift_follows', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('shift_follows', 'status_minutes')) {
                $table->dropColumn('status_minutes');
            }
        });
    }
};
