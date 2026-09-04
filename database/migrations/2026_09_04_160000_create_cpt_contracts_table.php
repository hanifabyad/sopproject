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
        Schema::create('cpt_contracts', function (Blueprint $table) {
            $table->id();
            $table->string('customer')->default('PT Patra Logistik');
            $table->string('contract_type')->nullable(); // Kontrak, Addendum, SPMP, etc.
            $table->text('project_title')->nullable();
            $table->string('project_name')->nullable();
            $table->string('project_number')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('status', ['active', 'expired', 'still_not_yet', 'completed'])->default('active');
            $table->text('notes')->nullable();
            $table->string('document_file')->nullable(); // Uploaded file path
            $table->text('document_link')->nullable(); // External Google Drive / cloud link
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cpt_contracts');
    }
};
