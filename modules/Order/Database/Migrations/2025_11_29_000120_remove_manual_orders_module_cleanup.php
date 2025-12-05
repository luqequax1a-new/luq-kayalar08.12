<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\User\Entities\Role;

return new class extends Migration
{
    public function up(): void
    {
        try {
            DB::table('orders')->where('created_from', 'admin_manual')->delete();
        } catch (\Throwable $e) {
            // ignore
        }

        $role = Role::whereTranslation('name', 'Admin')->first();
        if ($role) {
            $existing = is_array($role->permissions) ? $role->permissions : (json_decode($role->permissions, true) ?: []);
            unset($existing['admin.manual_orders.create']);
            $role->permissions = $existing;
            $role->save();
        }
    }

    public function down(): void
    {
        // no-op
    }
};

