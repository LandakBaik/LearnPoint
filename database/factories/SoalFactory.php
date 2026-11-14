<?php

namespace Database\Factories;

use App\Models\Quiz;
use App\Models\Soal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Soal>
 */
class SoalFactory extends Factory
{
    protected $model = Soal::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $keys = ['a', 'b', 'c', 'd'];
        $correctKey = fake()->randomElement($keys);

        return [
            'pertanyaan'    => fake()->sentence(8) . '?',
            'tipe_soal'     => 'single_choice',
            'pilihan'       => [
                'a' => fake()->sentence(3),
                'b' => fake()->sentence(3),
                'c' => fake()->sentence(3),
                'd' => fake()->sentence(3),
            ],
            'kunci_jawaban' => $correctKey,
            'bobot'         => 10,
            'urutan'        => 1,
            'kesulitan'     => 'Mudah',
            'level'         => 'Level 1',
            'quiz_id'       => Quiz::first()?->id ?? 1,
            'tugas_id'      => null,
            'ujian_id'      => null,
            'gambar'        => null,
        ];
    }
}

