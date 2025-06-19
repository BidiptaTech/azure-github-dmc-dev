<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // List of permissions to create
        $permissions = [
            // Customer permissions
            'create customer', 'edit customer', 'delete customer', 'view customer',

            // User permissions
            'create users', 'edit users', 'delete users', 'view users',

            // Role permissions
            'create roles', 'edit roles', 'delete roles', 'view roles',

            // Feature permissions
            'feature status', 'view features',

            // Category permissions
            'create category', 'edit category', 'delete category', 'view category',

            // Facility permissions
            'create facility', 'edit facility', 'delete facility', 'view facility',

            // Hotel permissions
            'create hotel', 'edit hotel', 'delete hotel', 'view hotel',

            // Room permissions
            'create room', 'edit room', 'delete room', 'view room',

            // Bed permissions
            'create bed', 'edit bed', 'delete bed', 'view bed',

            // Meal permissions
            'create meal', 'edit meal', 'delete meal', 'view meal',

            // Attraction permissions
            'create attraction', 'edit attraction', 'delete attraction', 'view attraction',

            // Guide permissions
            'create guide', 'edit guide', 'delete guide', 'view guide',

            // Transport permissions
            'create transport', 'edit transport', 'delete transport', 'view transport',

            // Restaurant permissions
            'create restaurant', 'edit restaurant', 'delete restaurant', 'view restaurant',

            // Driver permissions
            'create driver', 'edit driver', 'delete driver', 'view driver',

            // Vehicle permissions
            'create vehicle', 'edit vehicle', 'delete vehicle', 'view vehicle',

            // Country permissions
            'create country', 'edit country', 'delete country', 'view country',

            // Settings permissions
            'edit settings',

            // Transaction permissions
            'view transaction',

            // Tour permissions
            'view tour',
        ];

        foreach ($permissions as $permissionName) {
            // Check if the permission already exists by name
            if (!Permission::where('name', $permissionName)->exists()) {
                // Create permission only if it doesn't already exist
                Permission::create(['name' => $permissionName]);
            }
        }

        // You can continue with role creation if needed...
    }
}
