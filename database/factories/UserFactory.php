<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
            'email' => fake()->unique()->userName() . '@gmail.com',
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => 'admin',
        ];
    }

    /**
     * Atur email agar menggunakan domain @gmail.com atau email custom.
     */
    public function gmail(?string $username = null): static
    {
        return $this->state(fn (array $attributes) => [
            'email' => $username 
                ? (str_contains($username, '@') ? $username : $username . '@gmail.com')
                : fake()->unique()->userName() . '@gmail.com',
        ]);
    }

    /**
     * Atur password custom.
     */
    public function password(string $password): static
    {
        return $this->state(fn (array $attributes) => [
            'password' => Hash::make($password),
        ]);
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
     * Atur role admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }

    /**
     * Atur role kasir.
     */
    public function kasir(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'kasir',
        ]);
    }
}
