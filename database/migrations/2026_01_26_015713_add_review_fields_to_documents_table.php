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
        // Menyimpan isi komentar dari Ka.Dept
        $table->text('review_notes')->nullable()->after('status');
        // Mencatat ID user (Ka.Dept) yang memberikan review
        $table->foreignId('reviewed_by')->nullable()->constrained('users')->after('review_notes');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            //
        });
    }
};
