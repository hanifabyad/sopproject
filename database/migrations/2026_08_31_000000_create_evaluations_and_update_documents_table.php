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
        // 1. Create the new evaluations table first
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->onDelete('cascade');
            $table->foreignId('evaluator_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('evaluation_period', 4); // E.g., '2026'
            $table->date('due_date');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->string('status', 50)->default('upcoming'); // upcoming, due, in_review, submitted, admin_review, completed, overdue

            // Form Evaluasi Data
            $table->string('usage_status', 100)->nullable();
            $table->text('usage_reason')->nullable();
            
            $table->string('conformity_status', 100)->nullable();
            $table->text('conformity_notes')->nullable();
            
            $table->string('process_change_status', 50)->nullable();
            $table->text('process_change_notes')->nullable();
            
            $table->string('effectiveness_status', 100)->nullable();
            $table->text('implementation_issues')->nullable();
            
            $table->text('recommendation')->nullable();
            
            $table->string('result', 50)->nullable(); // CONTINUE, REVISION REQUIRED, NOT USED, OBSOLETE
            
            // Admin Action Data
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('admin_reviewed_at')->nullable();
            $table->text('admin_notes')->nullable();

            $table->timestamps();

            // Constraint agar tidak ada 2 evaluasi aktif untuk dokumen & periode tahun yang sama
            $table->unique(['document_id', 'evaluation_period']);
        });

        // 2. Add columns to documents table
        Schema::table('documents', function (Blueprint $table) {
            $table->date('effective_date')->nullable()->after('doc_date');
            $table->string('evaluation_status', 50)->default('upcoming')->after('status');
            $table->date('evaluation_due_date')->nullable()->after('effective_date');
            $table->foreignId('evaluation_id')->nullable()->after('evaluation_status')->constrained('evaluations')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['evaluation_id']);
            $table->dropColumn(['effective_date', 'evaluation_status', 'evaluation_due_date', 'evaluation_id']);
        });

        Schema::dropIfExists('evaluations');
    }
};
