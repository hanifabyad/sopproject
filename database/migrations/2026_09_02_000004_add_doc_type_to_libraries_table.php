<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('libraries', function (Blueprint $table) {
            if (!Schema::hasColumn('libraries', 'doc_type')) {
                $table->string('doc_type', 50)->nullable()->default('sop')->after('category');
            }
            if (!Schema::hasColumn('libraries', 'doc_number')) {
                $table->string('doc_number', 100)->nullable()->after('title');
            }
        });
    }

    public function down(): void
    {
        Schema::table('libraries', function (Blueprint $table) {
            if (Schema::hasColumn('libraries', 'doc_type')) {
                $table->dropColumn('doc_type');
            }
            if (Schema::hasColumn('libraries', 'doc_number')) {
                $table->dropColumn('doc_number');
            }
        });
    }
};
