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
        Schema::table('new_sop_requests', function (Blueprint $table) {
            $table->foreignId('document_id')->nullable()->after('status')->constrained('documents')->onDelete('set null');
            $table->text('revision_notes')->nullable()->after('admin_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('new_sop_requests', function (Blueprint $table) {
            $table->dropForeign(['document_id']);
            $table->dropColumn(['document_id', 'revision_notes']);
        });
    }
};
