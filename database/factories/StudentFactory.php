<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'LongName' => fake()->name(),
            'ShortName' => fake()->firstName(),
            'Dob' => fake()->date(),
            'EnrollDate' => now()->toDateString(),
            'nama_orang_tua' => fake()->name(),
            'Address' => fake()->streetAddress(),
            'City' => 'Jakarta',
            'kode_pos' => '14480',
            'Phone1' => '081234567890',
            'Whatsapp' => '081234567890',
            'Email' => fake()->safeEmail(),
            'Status' => 'aktif',
            'Quota' => 0,
            'is_new' => 0,
            'age' => 8,
        ];
    }
}
