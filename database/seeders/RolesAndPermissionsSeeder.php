<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Exceptions\RoleAlreadyExists;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $roles = config('permission.roles');

        foreach ($roles as $role) {
            try {
                $createdRole = Role::create([
                    'name' => $role['name'],
                    'guard_name' => $this->guardName()
                ]);
                $createdRole->syncPermissions(
                    $this->createPermissions($role['permissions'], $this->guardName())
                );
            } catch (RoleAlreadyExists $exception) {
                Role::findByName($role['name'], $this->guardName())
                    ->syncPermissions(
                        $this->createPermissions($role['permissions'], $this->guardName())
                    );
            }
        }
    }

    private function guardName()
    {
        return 'api';
    }

    private function createPermissions(array $permissions, string $guardName = null)
    {
        $createdPermissions = [];

        foreach ($permissions as $resource => $values) {

            $permissionNames = explode('|', $values);

            foreach ($permissionNames as $name) {
                $createdPermissions[] = $this->createPermission("{$name} {$resource}", $guardName);
            }
        }

        return $createdPermissions;
    }

    private function createPermission(string $name, string $guardName = null)
    {
        try {
            return Permission::findByName($name, $guardName);
        } catch (PermissionDoesNotExist $permissionDoesNotExist) {
            return Permission::create([
                'name' => $name,
                'guard_name' => $guardName ? $guardName : $this->guardName()
            ]);
        }
    }
}
