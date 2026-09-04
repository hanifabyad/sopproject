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
        Schema::table('socialization_attendance_participants', function (Blueprint $table) {
            $table->integer('quiz_score')->nullable()->after('status');
            $table->string('quiz_status', 30)->nullable()->after('quiz_score'); // 'passed', 'failed'
            $table->json('quiz_answers')->nullable()->after('quiz_status');
            $table->timestamp('quiz_attempted_at')->nullable()->after('quiz_answers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('socialization_attendance_participants', function (Blueprint $table) {
            $table->dropColumn(['quiz_score', 'quiz_status', 'quiz_answers', 'quiz_attempted_at']);
        });
    }
};
