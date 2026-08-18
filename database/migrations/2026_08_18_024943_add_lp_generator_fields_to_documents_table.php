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
            $table->string('doc_number')->nullable()->after('title');
            $table->string('doc_revision')->nullable()->after('doc_number');
            $table->string('doc_date')->nullable()->after('doc_revision');
            $table->string('company_header')->nullable()->after('doc_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['doc_number', 'doc_revision', 'doc_date', 'company_header']);
        });
    }
};
