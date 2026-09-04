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
            $table->text('sla_notes')->nullable()->after('evaluation_id');
            $table->foreignId('sla_action_by')->nullable()->after('sla_notes')->constrained('users')->onDelete('set null');
            $table->timestamp('sla_action_at')->nullable()->after('sla_action_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['sla_action_by']);
            $table->dropColumn(['sla_notes', 'sla_action_by', 'sla_action_at']);
        });
    }
};
