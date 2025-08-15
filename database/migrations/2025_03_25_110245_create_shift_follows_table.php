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
        Schema::create('shift_follows', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id')->nullable();
            $table->integer('branch_id')->nullable();
            $table->integer('zone_id')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('shift_id')->nullable();
            $table->dateTime('transaction_date')->nullable();
            $table->integer('shift_follow_type_id')->nullable();
            $table->string('ip_address', 50)->nullable();
            $table->string('user_agent')->nullable();
            $table->geometry('positions')->nullable();
            $table->boolean('is_offline')->default(false);
            $table->string('device_id', 255)->nullable();
            $table->string('device_model', 255)->nullable();
            $table->text('note')->nullable();
            $table->tinyInteger('is_qr')->default(0);
            $table->string('qr_type', 255)->nullable();
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
        Schema::dropIfExists('shift_follows');
    }
};
