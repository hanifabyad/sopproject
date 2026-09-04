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
        // 1. Tabel Bukti Sosialisasi SOP
        Schema::create('document_socializations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->date('socialization_date');
            $table->text('notes')->nullable();
            $table->string('attendance_file');
            $table->json('photos');
            $table->string('status', 50)->default('submitted'); // submitted, verified
            $table->timestamps();
        });

        // 2. Tabel Pengajuan Request Revisi SOP oleh User (Jendela 7 Hari)
        Schema::create('revision_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('reason');
            $table->string('status', 50)->default('pending'); // pending, approved, rejected, completed, expired
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('deadline_at')->nullable(); // Maksimal 7 hari dari approval
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });

        // 3. Tambahkan kolom pendukung ke documents table
        Schema::table('documents', function (Blueprint $table) {
            $table->string('socialization_status', 50)->default('pending')->after('sla_action_at');
            $table->timestamp('revision_deadline')->nullable()->after('socialization_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['socialization_status', 'revision_deadline']);
        });

        Schema::dropIfExists('revision_requests');
        Schema::dropIfExists('document_socializations');
    }
};
