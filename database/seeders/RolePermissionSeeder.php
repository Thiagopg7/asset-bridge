<?php

namespace Database\Seeders;

use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Seed the roles and permissions.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = collect(PermissionEnum::values())
            ->mapWithKeys(fn (string $permission) => [
                $permission => Permission::findOrCreate($permission, 'web'),
            ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (RoleEnum::cases() as $role) {
            Role::findOrCreate($role->value, 'web')
                ->syncPermissions(
                    $permissions->only($this->permissionsFor($role))->values(),
                );
        }
    }

    /**
     * Permissions granted to the given role.
     *
     * @return array<int, string>
     */
    private function permissionsFor(RoleEnum $role): array
    {
        return match ($role) {
            RoleEnum::Admin => PermissionEnum::values(),
            RoleEnum::Diretor => [
                PermissionEnum::BranchesView->value,
                PermissionEnum::UsersView->value,
                PermissionEnum::RequestsApprove->value,
                PermissionEnum::TransfersAuthorize->value,
                PermissionEnum::DispatchExecute->value,
            ],
            RoleEnum::Gerente => [
                PermissionEnum::BranchesView->value,
                PermissionEnum::UsersView->value,
                PermissionEnum::RequestsApprove->value,
                PermissionEnum::TransfersAuthorize->value,
            ],
            RoleEnum::Colaborador => [
                PermissionEnum::BranchesView->value,
            ],
        };
    }
}
