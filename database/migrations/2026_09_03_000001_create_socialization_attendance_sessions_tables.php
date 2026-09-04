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
        // 1. Sesi Presensi QR Sosialisasi
        Schema::create('socialization_attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('token', 32)->unique();
            $table->foreignId('document_id')->nullable()->constrained('documents')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('company', 20)->default('pkm');
            $table->string('agenda');
            $table->string('doc_number', 100)->nullable();
            $table->date('session_date');
            $table->string('session_time', 100)->nullable();
            $table->string('location', 200)->nullable();
            $table->string('speaker', 150)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Daftar Peserta Presensi
        Schema::create('socialization_attendance_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('socialization_attendance_sessions')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('name');
            $table->string('department'); // Jabatan / Bagian / Unit
            $table->string('status', 50)->default('Hadir');
            $table->timestamp('attended_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('socialization_attendance_participants');
        Schema::dropIfExists('socialization_attendance_sessions');
    }
};
