<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->datetime('start_date');
            $table->datetime('end_date');
            $table->string('send_type'); // all, role, branch, department

            // Rol bazlı gönderim
            $table->json('roles')->nullable();
            $table->enum('role_user_type', ['all', 'specific'])->nullable();
            $table->json('role_users')->nullable();

            // Şube bazlı gönderim
            $table->json('branches')->nullable();
            $table->enum('branch_user_type', ['all', 'specific'])->nullable();
            $table->json('branch_users')->nullable();

            // Departman bazlı gönderim
            $table->json('departments')->nullable();
            $table->enum('department_user_type', ['all', 'specific'])->nullable();
            $table->json('department_users')->nullable();

            // Hedef kullanıcılar (seçilen kullanıcıların birleşimi)
            $table->json('users')->nullable();

            $table->boolean('status')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->unsignedBigInteger('company_id')->nullable(); // Şirket ID'si eklendi
            $table->timestamps();
            $table->softDeletes();
        });

        // Duyuru-Kullanıcı pivot tablosu
        Schema::create('announcement_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('announcement_id');
            $table->unsignedBigInteger('user_id');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->unique(['announcement_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('announcement_user');
        Schema::dropIfExists('announcements');
    }
};
