<?php

use Illuminate\Database\Migrations\Migration;
use Modules\User\Entities\Role;

return new class extends Migration
{
    public function up(): void
    {
        $role = Role::whereTranslation('name', 'Admin')->first();
        if (!$role) {
            return;
        }

        $existing = is_array($role->permissions) ? $role->permissions : (json_decode($role->permissions, true) ?: []);
        $existing['admin.manual_orders.create'] = true;
        $existing['admin.cart_links.create'] = true;

        $role->permissions = $existing;
        $role->save();
    }

    public function down(): void
    {
        $role = Role::whereTranslation('name', 'Admin')->first();
        if (!$role) {
            return;
        }

        $existing = is_array($role->permissions) ? $role->permissions : (json_decode($role->permissions, true) ?: []);
        unset($existing['admin.manual_orders.create'], $existing['admin.cart_links.create']);

        $role->permissions = $existing;
        $role->save();
    }
};
