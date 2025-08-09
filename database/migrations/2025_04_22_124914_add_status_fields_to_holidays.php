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
        Schema::table('holidays', function (Blueprint $table) {
            // Durum açıklaması (onaylama/reddetme nedeni)
            $table->text('status_description')->nullable()->after('status');

            // Durumu değiştiren kullanıcı
            $table->integer('status_changed_by')->nullable()->after('status_description');

            // Durum değişikliği zamanı
            $table->timestamp('status_changed_at')->nullable()->after('status_changed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('holidays', function (Blueprint $table) {
            $table->dropColumn('status_description');
            $table->dropColumn('status_changed_by');
            $table->dropColumn('status_changed_at');
        });
    }
};
