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
    Schema::create('documents', function (Blueprint $table) {
        $table->id();
        $table->string('title'); // Judul SOP (Contoh: SOP perencanaan kerja V2)
        $table->string('department'); // Contoh: Human Capital
        
        // Lokasi file PDF hasil gabungan (Cover + Isi + Lampiran)
        $table->string('file_path')->nullable(); 
        
        // Logika Approval (0 = Belum Approve, 1 = Sudah Approve)
        $table->boolean('app_marine_supt')->default(0);
        $table->boolean('app_kabu')->default(0);
        $table->boolean('app_hse')->default(0);
        $table->boolean('app_direktur')->default(0);

        // Kolom untuk menentukan warna label di desain Anda
        // status: active (hijau), waiting (kuning), inactive (merah)
        $table->enum('status', ['active', 'waiting', 'inactive'])->default('waiting');
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
