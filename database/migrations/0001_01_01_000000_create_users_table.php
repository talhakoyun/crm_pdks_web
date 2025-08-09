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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('role_id')->nullable();
            $table->bigInteger('company_id')->nullable();
            $table->bigInteger('branch_id')->nullable();
            $table->bigInteger('department_id')->nullable();
            $table->bigInteger('zone_id')->nullable();
            $table->string('name', 255)->nullable();
            $table->string('surname', 255)->nullable();
            $table->string('email', 191)->nullable();
            $table->string('tc', 11)->nullable();
            $table->string('phone', 191)->nullable();
            $table->string('title', 255)->nullable();
            $table->integer('gender')->nullable();
            $table->date('birth_date')->nullable();
            $table->date('start_work_date')->nullable();
            $table->date('leave_work_date')->nullable();
            $table->string('password', 191)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('photo', 255)->nullable();
            $table->date('birthday')->nullable();
            $table->string('last_login', 191)->nullable();
            $table->string('last_wrong_login', 191)->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->bigInteger('deleted_by')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
