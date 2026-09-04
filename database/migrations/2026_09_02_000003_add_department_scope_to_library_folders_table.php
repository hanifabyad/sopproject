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
        Schema::table('library_folders', function (Blueprint $table) {
            $table->string('category')->nullable()->after('name');
            $table->string('division')->nullable()->after('category');
            $table->string('department')->nullable()->after('division');
            $table->string('business_unit')->nullable()->after('department');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('library_folders', function (Blueprint $table) {
            $table->dropColumn(['category', 'division', 'department', 'business_unit']);
        });
    }
};
