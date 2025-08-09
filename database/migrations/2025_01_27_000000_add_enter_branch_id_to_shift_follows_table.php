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
        Schema::table('shift_follows', function (Blueprint $table) {
            $table->integer('enter_branch_id')->nullable()->after('branch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shift_follows', function (Blueprint $table) {
            $table->dropColumn('enter_branch_id');
        });
    }
};
