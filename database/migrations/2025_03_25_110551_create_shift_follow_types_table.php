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
        Schema::create('shift_follow_types', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id');
            $table->integer('branch_id');
            $table->string('title', 255)->nullable();
            $table->string('type', 255)->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_follow_types');
    }
};
