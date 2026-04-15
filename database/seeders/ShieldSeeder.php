<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use BezhanSalleh\FilamentShield\Support\Utils;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $tenants = '[]';
        $users = '[]';
        $userTenantPivot = '[]';
        $rolesWithPermissions = '[{"name":"super_admin","guard_name":"web","permissions":["ViewAny:Devotional","View:Devotional","Create:Devotional","Update:Devotional","Delete:Devotional","DeleteAny:Devotional","Restore:Devotional","ForceDelete:Devotional","ForceDeleteAny:Devotional","RestoreAny:Devotional","Replicate:Devotional","Reorder:Devotional","ViewAny:Permission","View:Permission","Create:Permission","Update:Permission","Delete:Permission","DeleteAny:Permission","Restore:Permission","ForceDelete:Permission","ForceDeleteAny:Permission","RestoreAny:Permission","Replicate:Permission","Reorder:Permission","ViewAny:Role","View:Role","Create:Role","Update:Role","Delete:Role","DeleteAny:Role","Restore:Role","ForceDelete:Role","ForceDeleteAny:Role","RestoreAny:Role","Replicate:Role","Reorder:Role","ViewAny:User","View:User","Create:User","Update:User","Delete:User","DeleteAny:User","Restore:User","ForceDelete:User","ForceDeleteAny:User","RestoreAny:User","Replicate:User","Reorder:User","View:DevotionalsOverview"]},{"name":"panel_user","guard_name":"web","permissions":["view_user"]}]';
        $directPermissions = '{"0":{"name":"view_devotional","guard_name":"web"},"1":{"name":"view_any_devotional","guard_name":"web"},"2":{"name":"create_devotional","guard_name":"web"},"3":{"name":"update_devotional","guard_name":"web"},"4":{"name":"delete_devotional","guard_name":"web"},"5":{"name":"restore_devotional","guard_name":"web"},"6":{"name":"force_delete_devotional","guard_name":"web"},"8":{"name":"view_any_user","guard_name":"web"},"9":{"name":"create_user","guard_name":"web"},"10":{"name":"update_user","guard_name":"web"},"11":{"name":"delete_user","guard_name":"web"},"12":{"name":"restore_user","guard_name":"web"},"13":{"name":"force_delete_user","guard_name":"web"},"14":{"name":"view_role","guard_name":"web"},"15":{"name":"view_any_role","guard_name":"web"},"16":{"name":"create_role","guard_name":"web"},"17":{"name":"update_role","guard_name":"web"},"18":{"name":"delete_role","guard_name":"web"},"19":{"name":"restore_role","guard_name":"web"},"20":{"name":"force_delete_role","guard_name":"web"},"70":{"name":"delete_any_devotional","guard_name":"web"},"71":{"name":"force_delete_any_devotional","guard_name":"web"},"72":{"name":"restore_any_devotional","guard_name":"web"},"73":{"name":"replicate_devotional","guard_name":"web"},"74":{"name":"reorder_devotional","guard_name":"web"},"75":{"name":"view_any_permission","guard_name":"web"},"76":{"name":"view_permission","guard_name":"web"},"77":{"name":"create_permission","guard_name":"web"},"78":{"name":"update_permission","guard_name":"web"},"79":{"name":"delete_permission","guard_name":"web"},"80":{"name":"delete_any_permission","guard_name":"web"},"81":{"name":"restore_permission","guard_name":"web"},"82":{"name":"force_delete_permission","guard_name":"web"},"83":{"name":"force_delete_any_permission","guard_name":"web"},"84":{"name":"restore_any_permission","guard_name":"web"},"85":{"name":"replicate_permission","guard_name":"web"},"86":{"name":"reorder_permission","guard_name":"web"},"87":{"name":"delete_any_role","guard_name":"web"},"88":{"name":"force_delete_any_role","guard_name":"web"},"89":{"name":"restore_any_role","guard_name":"web"},"90":{"name":"replicate_role","guard_name":"web"},"91":{"name":"reorder_role","guard_name":"web"},"92":{"name":"delete_any_user","guard_name":"web"},"93":{"name":"force_delete_any_user","guard_name":"web"},"94":{"name":"restore_any_user","guard_name":"web"},"95":{"name":"replicate_user","guard_name":"web"},"96":{"name":"reorder_user","guard_name":"web"},"97":{"name":"view_devotionals_overview","guard_name":"web"}}';

        // 1. Seed tenants first (if present)
        if (! blank($tenants) && $tenants !== '[]') {
            static::seedTenants($tenants);
        }

        // 2. Seed roles with permissions
        static::makeRolesWithPermissions($rolesWithPermissions);

        // 3. Seed direct permissions
        static::makeDirectPermissions($directPermissions);

        // 4. Seed users with their roles/permissions (if present)
        if (! blank($users) && $users !== '[]') {
            static::seedUsers($users);
        }

        // 5. Seed user-tenant pivot (if present)
        if (! blank($userTenantPivot) && $userTenantPivot !== '[]') {
            static::seedUserTenantPivot($userTenantPivot);
        }

        $this->command->info('Shield Seeding Completed.');
    }

    protected static function seedTenants(string $tenants): void
    {
        if (blank($tenantData = json_decode($tenants, true))) {
            return;
        }

        $tenantModel = '';
        if (blank($tenantModel)) {
            return;
        }

        foreach ($tenantData as $tenant) {
            $tenantModel::firstOrCreate(
                ['id' => $tenant['id']],
                $tenant
            );
        }
    }

    protected static function seedUsers(string $users): void
    {
        if (blank($userData = json_decode($users, true))) {
            return;
        }

        $userModel = 'App\Models\User';
        $tenancyEnabled = false;

        foreach ($userData as $data) {
            // Extract role/permission data before creating user
            $roles = $data['roles'] ?? [];
            $permissions = $data['permissions'] ?? [];
            $tenantRoles = $data['tenant_roles'] ?? [];
            $tenantPermissions = $data['tenant_permissions'] ?? [];
            unset($data['roles'], $data['permissions'], $data['tenant_roles'], $data['tenant_permissions']);

            $user = $userModel::firstOrCreate(
                ['email' => $data['email']],
                $data
            );

            // Handle tenancy mode - sync roles/permissions per tenant
            if ($tenancyEnabled && (! empty($tenantRoles) || ! empty($tenantPermissions))) {
                foreach ($tenantRoles as $tenantId => $roleNames) {
                    $contextId = $tenantId === '_global' ? null : $tenantId;
                    setPermissionsTeamId($contextId);
                    $user->syncRoles($roleNames);
                }

                foreach ($tenantPermissions as $tenantId => $permissionNames) {
                    $contextId = $tenantId === '_global' ? null : $tenantId;
                    setPermissionsTeamId($contextId);
                    $user->syncPermissions($permissionNames);
                }
            } else {
                // Non-tenancy mode
                if (! empty($roles)) {
                    $user->syncRoles($roles);
                }

                if (! empty($permissions)) {
                    $user->syncPermissions($permissions);
                }
            }
        }
    }

    protected static function seedUserTenantPivot(string $pivot): void
    {
        if (blank($pivotData = json_decode($pivot, true))) {
            return;
        }

        $pivotTable = '';
        if (blank($pivotTable)) {
            return;
        }

        foreach ($pivotData as $row) {
            $uniqueKeys = [];

            if (isset($row['user_id'])) {
                $uniqueKeys['user_id'] = $row['user_id'];
            }

            $tenantForeignKey = 'team_id';
            if (! blank($tenantForeignKey) && isset($row[$tenantForeignKey])) {
                $uniqueKeys[$tenantForeignKey] = $row[$tenantForeignKey];
            }

            if (! empty($uniqueKeys)) {
                DB::table($pivotTable)->updateOrInsert($uniqueKeys, $row);
            }
        }
    }

    protected static function makeRolesWithPermissions(string $rolesWithPermissions): void
    {
        if (blank($rolePlusPermissions = json_decode($rolesWithPermissions, true))) {
            return;
        }

        /** @var \Illuminate\Database\Eloquent\Model $roleModel */
        $roleModel = Utils::getRoleModel();
        /** @var \Illuminate\Database\Eloquent\Model $permissionModel */
        $permissionModel = Utils::getPermissionModel();

        $tenancyEnabled = false;
        $teamForeignKey = 'team_id';

        foreach ($rolePlusPermissions as $rolePlusPermission) {
            $tenantId = $rolePlusPermission[$teamForeignKey] ?? null;

            // Set tenant context for role creation and permission sync
            if ($tenancyEnabled) {
                setPermissionsTeamId($tenantId);
            }

            $roleData = [
                'name' => $rolePlusPermission['name'],
                'guard_name' => $rolePlusPermission['guard_name'],
            ];

            // Include tenant ID in role data (can be null for global roles)
            if ($tenancyEnabled && ! blank($teamForeignKey)) {
                $roleData[$teamForeignKey] = $tenantId;
            }

            $role = $roleModel::firstOrCreate($roleData);

            if (! blank($rolePlusPermission['permissions'])) {
                $permissionModels = collect($rolePlusPermission['permissions'])
                    ->map(fn ($permission) => $permissionModel::firstOrCreate([
                        'name' => $permission,
                        'guard_name' => $rolePlusPermission['guard_name'],
                    ]))
                    ->all();

                $role->syncPermissions($permissionModels);
            }
        }
    }

    public static function makeDirectPermissions(string $directPermissions): void
    {
        if (blank($permissions = json_decode($directPermissions, true))) {
            return;
        }

        /** @var \Illuminate\Database\Eloquent\Model $permissionModel */
        $permissionModel = Utils::getPermissionModel();

        foreach ($permissions as $permission) {
            if ($permissionModel::whereName($permission['name'])->doesntExist()) {
                $permissionModel::create([
                    'name' => $permission['name'],
                    'guard_name' => $permission['guard_name'],
                ]);
            }
        }
    }
}
