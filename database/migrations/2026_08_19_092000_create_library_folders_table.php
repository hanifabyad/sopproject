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
        Schema::create('library_folders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->timestamps();

            $table->foreign('parent_id')
                  ->references('id')
                  ->on('library_folders')
                  ->onDelete('cascade');
        });

        Schema::create('library_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('folder_id');
            $table->string('name');
            $table->string('path');
            $table->string('mime_type');
            $table->bigInteger('size');
            $table->unsignedBigInteger('uploaded_by');
            $table->timestamps();

            $table->foreign('folder_id')
                  ->references('id')
                  ->on('library_folders')
                  ->onDelete('cascade');

            $table->foreign('uploaded_by')
                  ->references('id')
                  ->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('library_files');
        Schema::dropIfExists('library_folders');
    }
};
