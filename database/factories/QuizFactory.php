<?php

namespace Database\Factories;

use App\Models\PengampuKelas;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Quiz>
 */
class QuizFactory extends Factory
{
    protected $model = Quiz::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'judul'             => 'Kuis Petualangan ' . fake()->words(3, true),
            'deskripsi'         => fake()->sentence(10),
            'status'            => 'published',
            'kesulitan_aktif'   => ['Mudah', 'Sedang', 'Sulit'],
            'waktu_mulai'       => now()->subDay(),
            'waktu_selesai'     => now()->addDays(30),
            'acak_jawaban'      => true,
            'level'             => 'Level 1',
            'kesulitan'         => 'Mudah',
            'pengampu_kelas_id' => PengampuKelas::first()?->id ?? 1,
        ];
    }

    /**
     * State untuk kuis berstatus draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    /**
     * State untuk kuis berstatus published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
        ]);
    }
}

