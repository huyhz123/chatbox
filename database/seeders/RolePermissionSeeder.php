<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        // Create permissions
        $permissions = [
            // User permissions
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',

            // Role & Permission permissions
            'view-roles',
            'create-roles',
            'edit-roles',
            'delete-roles',

            // Category permissions
            'view-categories',
            'create-categories',
            'edit-categories',
            'delete-categories',

            // Service permissions
            'view-services',
            'create-services',
            'edit-services',
            'delete-services',

            // Product permissions
            'view-products',
            'create-products',
            'edit-products',
            'delete-products',

            // File permissions
            'view-files',
            'create-files',
            'edit-files',
            'delete-files',

            // Course permissions
            'view-courses',
            'create-courses',
            'edit-courses',
            'delete-courses',

            // Order permissions
            'view-orders',
            'create-orders',
            'edit-orders',
            'delete-orders',
            'mark-order-paid',
            'refund-orders',

            // Ticket permissions
            'view-tickets',
            'create-tickets',
            'edit-tickets',
            'delete-tickets',
            'process-tickets',

            // Payment permissions
            'view-payments',
            'process-payments',

            // Settings permissions
            'view-settings',
            'edit-settings',

            // Report permissions
            'view-reports',
            'export-reports',

            // Analytics permissions
            'view-analytics',

            // Activity log permissions
            'view-activity-logs',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // Create roles
        $superadminRole = Role::firstOrCreate([
            'name' => 'superadmin',
            'guard_name' => 'web',
        ]);

        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $customerRole = Role::firstOrCreate([
            'name' => 'customer',
            'guard_name' => 'web',
        ]);

        // Assign all permissions to superadmin
        $superadminRole->syncPermissions($permissions);

        // Assign admin permissions
        $adminPermissions = [
            'view-users',
            'create-users',
            'edit-users',
            'view-categories',
            'create-categories',
            'edit-categories',
            'view-services',
            'create-services',
            'edit-services',
            'view-products',
            'create-products',
            'edit-products',
            'view-files',
            'create-files',
            'edit-files',
            'view-courses',
            'create-courses',
            'edit-courses',
            'view-orders',
            'create-orders',
            'mark-order-paid',
            'view-tickets',
            'process-tickets',
            'view-payments',
            'view-settings',
            'edit-settings',
            'view-reports',
            'export-reports',
            'view-analytics',
            'view-activity-logs',
        ];
        $adminRole->syncPermissions($adminPermissions);

        // Assign customer permissions
        $customerPermissions = [
            'view-products',
            'view-services',
            'view-files',
            'view-courses',
            'create-orders',
            'view-orders',
            'create-tickets',
            'view-tickets',
            'view-payments',
        ];
        $customerRole->syncPermissions($customerPermissions);
    }
}
