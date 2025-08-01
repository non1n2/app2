<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Admin;
use App\Models\Customer;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
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
            'password' => Hash::make('password'), // the password is "password"
            'remember_token' => Str::random(10),
        ];
    }

    public function asAdmin()
    {
        return $this->state(function (array $attributes) {
            return [
                'userable_type' => Admin::class,
                'userable_id' => Admin::factory(),
            ];
        });
    }
    public function asCustomer()
    {
        return $this->state(function (array $attributes) {
            return [
                'userable_type' => Customer::class,
                'userable_id' => Customer::factory(),
            ];
        });
    }
}
