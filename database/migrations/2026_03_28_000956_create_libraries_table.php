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
        Schema::create('libraries', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Judul SOP
            $table->string('category'); // 'divisi' atau 'support'
            
            // Untuk Hierarki Divisi
            $table->string('division_name')->nullable(); // Retail, Komersil, dll
            $table->string('business_unit')->nullable(); // SPBU, LPG PSO, dll
            $table->string('company_name')->nullable();  // PT SCK, PT MMS, dll
            
            // Untuk Support (Referensi)
            $table->string('support_type')->nullable();  // HR, IT, Finance, dll

            $table->string('file_path'); // Lokasi file PDF hasil sah
            $table->unsignedBigInteger('uploaded_by'); // Admin yang memindahkan
            $table->integer('view_count')->default(0); // Berapa kali dilihat
            $table->timestamps();

            $table->foreign('uploaded_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('libraries');
    }
};
