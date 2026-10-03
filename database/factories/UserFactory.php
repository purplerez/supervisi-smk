<?php

namespace Database\Factories;

use App\Models\Sekolah;
use App\Models\User;
use App\Models\UserRole;
use App\Tenant\TenantContext;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sekolah_id' => fn () => TenantContext::get() ?? Sekolah::factory(),
            'nama' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'nip' => fake()->numerify('19##########'),
            'nuptk' => fake()->numerify('16##########'),
            'must_change_password' => true,
            'aktif' => true,
            'is_super_admin' => false,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Konfigurasi factory.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (User $user) {
            if ($user->is_super_admin) {
                return;
            }

            // Bila TenantContext belum di-set, sinkronkan ke sekolah user
            if (TenantContext::get() === null && $user->sekolah_id) {
                TenantContext::set($user->sekolah_id);
            }
        });
    }

    /**
     * Tautkan user ke sekolah tertentu.
     */
    public function forSekolah(Sekolah|int $sekolah): static
    {
        $id = $sekolah instanceof Sekolah ? $sekolah->id : $sekolah;

        return $this->state(fn () => [
            'sekolah_id' => $id,
        ]);
    }

    /**
     * State untuk Super Admin (sekolah_id null, is_super_admin true).
     */
    public function superAdmin(): static
    {
        return $this->state(fn () => [
            'is_super_admin' => true,
            'sekolah_id' => null,
            'username' => null,
            'email' => fake()->unique()->safeEmail(),
        ]);
    }

    /**
     * State dengan role admin sekolah.
     */
    public function admin(): static
    {
        return $this->afterCreating(function (User $user) {
            UserRole::firstOrCreate([
                'sekolah_id' => $user->sekolah_id,
                'user_id' => $user->id,
                'role' => 'admin',
            ]);
        });
    }

    /**
     * State dengan role supervisor.
     */
    public function supervisor(): static
    {
        return $this->afterCreating(function (User $user) {
            UserRole::firstOrCreate([
                'sekolah_id' => $user->sekolah_id,
                'user_id' => $user->id,
                'role' => 'supervisor',
            ]);
        });
    }

    /**
     * State dengan role guru.
     */
    public function guru(): static
    {
        return $this->afterCreating(function (User $user) {
            UserRole::firstOrCreate([
                'sekolah_id' => $user->sekolah_id,
                'user_id' => $user->id,
                'role' => 'guru',
            ]);
        });
    }

    /**
     * State kombinasi supervisor dan guru sekaligus.
     */
    public function supervisorDanGuru(): static
    {
        return $this->supervisor()->guru();
    }

    /**
     * Alias untuk supervisorDanGuru.
     */
    public function supervisorGuru(): static
    {
        return $this->supervisorDanGuru();
    }
}
