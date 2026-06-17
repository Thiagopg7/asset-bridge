<?php

namespace Database\Seeders;

use App\Enums\Role as RoleEnum;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Seed the users used for development and testing.
     *
     * Creates one global admin plus, for each branch, one gerente and three
     * colaboradores already assigned to that branch.
     */
    public function run(): void
    {
        $this->makeUser('Admin Geral', 'admin@asset-bridge.test', RoleEnum::Admin);

        foreach (Branch::all() as $branch) {
            $slug = Str::lower($branch->state);

            $this->makeUser(
                "Gerente {$branch->state}",
                "gerente.{$slug}@asset-bridge.test",
                RoleEnum::Gerente,
                $branch,
            );

            for ($i = 1; $i <= 3; $i++) {
                $this->makeUser(
                    "Colaborador {$branch->state} {$i}",
                    "colaborador{$i}.{$slug}@asset-bridge.test",
                    RoleEnum::Colaborador,
                    $branch,
                );
            }
        }
    }

    /**
     * Create (idempotently) a user with the given role and optional branch.
     */
    private function makeUser(string $name, string $email, RoleEnum $role, ?Branch $branch = null): void
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'branch_id' => $branch?->id,
            ],
        );

        $user->syncRoles([$role->value]);
    }
}
