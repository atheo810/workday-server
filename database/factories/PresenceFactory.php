<?php

namespace Database\Factories;

use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Presence>
 */
class PresenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $officeLatitude = -8.6908034;
        $officeLongitude = 115.2237311;
        return [
            'uuid' => $this->faker->uuid,
            'distance_in' => $this->faker->numberBetween(1, 100),
            'distance_out' => $this->faker->numberBetween(1, 100),
            // 'presence_by' => 1,
            "clock_in" => $this->faker->time(),
            "clock_out" => $this->faker->time(),
            "ip_address" => $this->faker->ipv4,
            'device' => $this->faker->randomElement(['web', 'android', 'ios']),
            'latitude_in' => $this->faker->randomFloat(6, -0.000898, 0.000898) + $officeLatitude,
            'longitude_in' => $this->faker->randomFloat(6, -0.000898, 0.000898) + $officeLongitude,
            'latitude_out' => $this->faker->randomFloat(6, -0.000898, 0.000898) + $officeLatitude,
            'longitude_out' => $this->faker->randomFloat(6, -0.000898, 0.000898) + $officeLongitude,
            "photo_in" => $this->faker->imageUrl(),
            "photo_out" => $this->faker->imageUrl(),
            'presence_date' => $this->faker->date(),
        ];
    }
}
