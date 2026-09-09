<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Services\RoleManagementService;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Delete user 'asrofi' or user with role 'CPT Maintenance'
        DB::table('users')
            ->where('username', 'asrofi')
            ->orWhereRaw('LOWER(TRIM(role)) = ?', ['cpt maintenance'])
            ->delete();

        // 2. Specific role renames
        DB::table('users')
            ->whereRaw('LOWER(TRIM(role)) IN (?, ?)', ['ka dept. legal & hc manager', 'legal & hc manager'])
            ->update(['role' => 'Legal & HC Manager']);

        DB::table('users')
            ->whereRaw('LOWER(TRIM(role)) = ?', ['cpt operasional'])
            ->update(['role' => 'Ka. Operasional CPT']);

        DB::table('users')
            ->whereRaw('LOWER(TRIM(role)) IN (?, ?)', ['chief f&a', 'chief f&a holding'])
            ->update(['role' => 'Chief F&A Holding']);

        // 3. Normalize remaining uppercase/mismatched roles to canonical Title Case
        $allRoles = RoleManagementService::getAllRoles();
        $users = DB::table('users')->select('id', 'role')->get();

        foreach ($users as $u) {
            if (!$u->role) {
                continue;
            }
            foreach ($allRoles as $canonical) {
                if (strcasecmp(trim($u->role), trim($canonical)) === 0) {
                    if ($u->role !== $canonical) {
                        DB::table('users')->where('id', $u->id)->update(['role' => $canonical]);
                    }
                    break;
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse needed for data synchronization
    }
};
