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
        Schema::table('document_approvals', function (Blueprint $table) {
            $table->unsignedSmallInteger('signature_page')->nullable()->after('signature_slot');
            $table->decimal('signature_x', 8, 2)->nullable()->after('signature_page');
            $table->decimal('signature_y', 8, 2)->nullable()->after('signature_x');
            $table->string('signature_anchor')->nullable()->after('signature_y');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_approvals', function (Blueprint $table) {
            $table->dropColumn(['signature_page', 'signature_x', 'signature_y', 'signature_anchor']);
        });
    }
};
