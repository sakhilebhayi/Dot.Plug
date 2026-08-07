<?php

namespace Database\Factories;

use App\Models\Extension;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Extension>
 */
class ExtensionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true).' Connector';
        $name = Str::title($name);

        return [
            'developer_team_id' => Team::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::random(4),
            'tagline' => $this->faker->sentence(6),
            'description' => $this->faker->paragraph(3),
            'category' => $this->faker->randomElement(['integrations', 'connectors', 'analytics', 'automation', 'vertical']),
            'status' => 'certified',
            'icon' => 'extension',
        ];
    }
}
