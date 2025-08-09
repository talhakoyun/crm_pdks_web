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
        Schema::create('user_shift_customs', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id')->nullable();
            $table->integer('branch_id')->nullable();
            $table->integer('user_id');
            $table->integer('shift_definition_id');
            $table->date('start_date');
            $table->date('end_date');
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
        Schema::dropIfExists('user_shift_customs');
    }
};
