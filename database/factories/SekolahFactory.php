<?php

namespace Database\Factories;

use App\Models\Sekolah;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sekolah>
 */
class SekolahFactory extends Factory
{
    protected $model = Sekolah::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => 'SMK Negeri 1 '.fake()->unique()->city(),
            'npsn' => fake()->unique()->numerify('########'),
            'kode' => fake()->unique()->slug(2),
            'alamat' => fake()->address(),
            'logo_path' => null,
            'status' => 'aktif',
        ];
    }

    /**
     * State sekolah berstatus nonaktif.
     */
    public function nonaktif(): static
    {
        return $this->state(fn () => [
            'status' => 'nonaktif',
        ]);
    }
}
