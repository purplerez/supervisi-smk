<?php

namespace Database\Factories;

use App\Models\Sekolah;
use App\Models\User;
use App\Models\UserRole;
use App\Tenant\TenantContext;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserRole>
 */
class UserRoleFactory extends Factory
{
    protected $model = UserRole::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sekolah_id' => fn () => TenantContext::get() ?? Sekolah::factory(),
            'user_id' => fn () => User::factory(),
            'role' => 'guru',
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => ['role' => 'admin']);
    }

    public function supervisor(): static
    {
        return $this->state(fn () => ['role' => 'supervisor']);
    }

    public function guru(): static
    {
        return $this->state(fn () => ['role' => 'guru']);
    }
}
