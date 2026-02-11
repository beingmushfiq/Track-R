<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SecurityPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating security permissions...');

        // Create Phase 1 Security Permissions
        $permissions = [
            // Panic Event Permissions
            'view panic events',
            'create panic events',
            'resolve panic events',
            'delete panic events',
            // Device Command Permissions
            'send device commands',
            'lock engine',
            'unlock engine',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
            $this->command->info("Created permission: {$permission}");
        }

        // Assign to existing Admin role if it exists
        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($permissions);
            $this->command->info('Assigned all permissions to Admin role');
        }

        // Assign to existing Manager role if it exists (limited permissions)
        $managerRole = Role::where('name', 'Manager')->first();
        if ($managerRole) {
            $managerRole->givePermissionTo([
                'view panic events',
                'resolve panic events',
                'send device commands',
                'lock engine',
                'unlock engine',
            ]);
            $this->command->info('Assigned permissions to Manager role');
        }

        $this->command->info('Security permissions created successfully!');
    }
}
