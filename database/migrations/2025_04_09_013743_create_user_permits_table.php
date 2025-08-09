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
        Schema::create('user_permits', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->tinyInteger('allow_outside');
            $table->tinyInteger('allow_offline');
            $table->tinyInteger('allow_zone');
            $table->tinyInteger('zone_flexible');
            $table->tinyInteger('is_active')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_permits');
    }
};
