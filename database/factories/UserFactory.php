<?php

namespace Database\Factories;

use App\Enums\Role as RoleEnum;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }

    /**
     * Assign the user to the given branch.
     */
    public function forBranch(Branch $branch): static
    {
        return $this->state(fn (array $attributes) => [
            'branch_id' => $branch->id,
        ]);
    }

    /**
     * Assign the admin role to the user.
     */
    public function admin(): static
    {
        return $this->withRole(RoleEnum::Admin);
    }

    /**
     * Assign the diretor role to the user.
     */
    public function diretor(): static
    {
        return $this->withRole(RoleEnum::Diretor);
    }

    /**
     * Assign the gerente role to the user.
     */
    public function gerente(): static
    {
        return $this->withRole(RoleEnum::Gerente);
    }

    /**
     * Assign the colaborador role to the user.
     */
    public function colaborador(): static
    {
        return $this->withRole(RoleEnum::Colaborador);
    }

    /**
     * Assign the logistica role to the user.
     */
    public function logistica(): static
    {
        return $this->withRole(RoleEnum::Logistica);
    }

    /**
     * Assign the given role to the user after creation, creating it if needed.
     */
    protected function withRole(RoleEnum $role): static
    {
        return $this->afterCreating(function (User $user) use ($role) {
            $user->assignRole(Role::findOrCreate($role->value, 'web'));
        });
    }
}
