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
        Schema::table('shift_definitions', function (Blueprint $table) {
            // Haftalık çalışma saatleri - her gün için başlangıç ve bitiş saati
            $table->time('monday_start')->nullable()->comment('Pazartesi başlangıç saati');
            $table->time('monday_end')->nullable()->comment('Pazartesi bitiş saati');
            $table->time('tuesday_start')->nullable()->comment('Salı başlangıç saati');
            $table->time('tuesday_end')->nullable()->comment('Salı bitiş saati');
            $table->time('wednesday_start')->nullable()->comment('Çarşamba başlangıç saati');
            $table->time('wednesday_end')->nullable()->comment('Çarşamba bitiş saati');
            $table->time('thursday_start')->nullable()->comment('Perşembe başlangıç saati');
            $table->time('thursday_end')->nullable()->comment('Perşembe bitiş saati');
            $table->time('friday_start')->nullable()->comment('Cuma başlangıç saati');
            $table->time('friday_end')->nullable()->comment('Cuma bitiş saati');
            $table->time('saturday_start')->nullable()->comment('Cumartesi başlangıç saati');
            $table->time('saturday_end')->nullable()->comment('Cumartesi bitiş saati');
            $table->time('sunday_start')->nullable()->comment('Pazar başlangıç saati');
            $table->time('sunday_end')->nullable()->comment('Pazar bitiş saati');

            // Eski alanları nullable yapalım (geriye uyumluluk için)
            $table->time('start_time')->nullable()->change();
            $table->time('end_time')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shift_definitions', function (Blueprint $table) {
            // Haftalık çalışma saatlerini kaldır
            $table->dropColumn([
                'monday_start', 'monday_end',
                'tuesday_start', 'tuesday_end',
                'wednesday_start', 'wednesday_end',
                'thursday_start', 'thursday_end',
                'friday_start', 'friday_end',
                'saturday_start', 'saturday_end',
                'sunday_start', 'sunday_end'
            ]);

            // Eski alanları geri required yapalım
            $table->time('start_time')->nullable(false)->change();
            $table->time('end_time')->nullable(false)->change();
        });
    }
};
