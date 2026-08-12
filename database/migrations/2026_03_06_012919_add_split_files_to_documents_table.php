<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
        public function up()
    {
        Schema::table('documents', function (Blueprint $table) {
            // Kolom untuk path file yang dipisah
            $table->string('file_cover')->after('reviewer_id')->nullable();
            $table->string('file_lp')->after('file_cover')->nullable();
            $table->string('file_isi')->after('file_lp')->nullable();
            $table->string('file_lampiran')->after('file_isi')->nullable();
            $table->string('file_final')->after('file_lampiran')->nullable();
            
            // TAMBAHKAN INI: Kolom untuk menyimpan koordinat khusus jika diperlukan
            $table->json('signature_positions')->after('file_final')->nullable();
        });
    }

        public function down()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn([
                'file_cover', 'file_lp', 'file_isi', 'file_lampiran', 'file_final', 'signature_positions'
            ]);
        });
    }
};
