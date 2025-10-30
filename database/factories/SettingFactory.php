<?php

namespace Sajdoko\TallPress\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Sajdoko\TallPress\Models\Setting;

class SettingFactory extends Factory
{
    protected $model = Setting::class;

    public function definition(): array
    {
        return [
            'key' => $this->faker->unique()->word,
            'value' => $this->faker->word,
            'type' => 'string',
            'group' => 'general',
            'autoload' => true,
        ];
    }

    public function boolean(): self
    {
        return $this->state(fn (array $attributes) => [
            'value' => $this->faker->boolean ? '1' : '0',
            'type' => 'boolean',
        ]);
    }

    public function integer(): self
    {
        return $this->state(fn (array $attributes) => [
            'value' => (string) $this->faker->numberBetween(1, 100),
            'type' => 'integer',
        ]);
    }

    public function group(string $group): self
    {
        return $this->state(fn (array $attributes) => [
            'group' => $group,
        ]);
    }
}
