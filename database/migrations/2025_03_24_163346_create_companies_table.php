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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->string('title', 255);
            $table->string('image', 255)->nullable();
            $table->string('phone_number', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('web_address', 255)->nullable();
            $table->string('address', 255)->nullable();
            $table->integer('employee_count')->default(0);
            $table->string('company_type', 255)->nullable();
            $table->integer('shift_start_tolerance')->default(0);
            $table->integer('shift_end_tolerance')->default(0);
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
        Schema::dropIfExists('companies');
    }
};
