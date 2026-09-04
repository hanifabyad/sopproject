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
        // 1. Tambah kolom tanggal obsolete ke documents
        Schema::table('documents', function (Blueprint $table) {
            $table->timestamp('obsolete_at')->nullable()->after('revision_deadline');
            $table->date('review_due_date')->nullable()->after('obsolete_at'); // 6-month review date
        });

        // 2. Tabel Kuis Pemahaman SOP (Poin 12)
        Schema::create('sop_quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->onDelete('cascade');
            $table->string('title');
            $table->integer('passing_score')->default(70); // KKM 70
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Tabel Soal Kuis (7 PG, 3 Essay)
        Schema::create('sop_quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sop_quiz_id')->constrained('sop_quizzes')->onDelete('cascade');
            $table->enum('type', ['multiple_choice', 'essay'])->default('multiple_choice');
            $table->text('question');
            $table->json('options')->nullable(); // Pilihan A, B, C, D untuk PG
            $table->string('correct_answer')->nullable(); // Kunci jawaban untuk PG
            $table->integer('points')->default(10);
            $table->integer('sequence')->default(1);
            $table->timestamps();
        });

        // 4. Tabel Hasil Ujian / Attempt User
        Schema::create('sop_quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sop_quiz_id')->constrained('sop_quizzes')->onDelete('cascade');
            $table->foreignId('document_id')->constrained('documents')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('score')->default(0);
            $table->enum('status', ['passed', 'failed', 'under_review'])->default('failed');
            $table->json('answers')->nullable();
            $table->timestamp('attempt_date')->useCurrent();
            $table->text('feedback')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sop_quiz_attempts');
        Schema::dropIfExists('sop_quiz_questions');
        Schema::dropIfExists('sop_quizzes');

        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['obsolete_at', 'review_due_date']);
        });
    }
};
