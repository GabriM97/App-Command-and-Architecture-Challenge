<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => Hash::make($this->faker->password),
            'activated_at' => $this->faker->optional(0.7)->dateTime,
            'banned_at' => $this->faker->optional(0.1)->dateTime,
            'deleted_at' => $this->faker->optional(0.2)->dateTime,
        ];
    }
}
