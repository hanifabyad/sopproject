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
        Schema::table('documents', function (Blueprint $table) {
        // Menambahkan kolom untuk menyimpan ID peninjau yang dipilih Admin
        $table->unsignedBigInteger('reviewer_id')->nullable()->after('department');
        
        // Opsional: Membuat relasi agar data lebih aman
        $table->foreign('reviewer_id')->references('id')->on('users')->onDelete('set null');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            //
        });
    }
};
