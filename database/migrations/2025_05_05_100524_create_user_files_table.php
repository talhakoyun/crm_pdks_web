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
        Schema::create('user_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('file_type_id')->nullable();
            $table->string('title', 255)->nullable();
            $table->string('filename', 255)->nullable();
            $table->string('original_filename', 255)->nullable();
            $table->string('file_path', 255)->nullable();
            $table->string('file_extension', 20)->nullable();
            $table->integer('file_size')->comment('Byte cinsinden dosya boyutu')->nullable();
            $table->text('description')->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->bigInteger('deleted_by')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_files');
    }
};
