<?php

namespace Database\Factories;

use App\Models\Extension;
use App\Models\ExtensionVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExtensionVersion>
 */
class ExtensionVersionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'extension_id' => Extension::factory(),
            'version' => '1.0.0',
            'changelog' => 'Initial release.',
            'is_current' => true,
        ];
    }
}
