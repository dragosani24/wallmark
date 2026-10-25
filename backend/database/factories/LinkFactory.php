<?php

namespace Database\Factories;

use App\Models\Link;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Link>
 */
class LinkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $domain = $this->faker->unique()->domainName();

        return [
            'url' => "https://{$domain}/".str($this->faker->unique()->slug(4))->append('/'.$this->faker->numberBetween(1000, 9999)),
            'title' => $this->faker->sentence(6),
            'description' => $this->faker->sentence(14),
            'image_url' => 'https://picsum.photos/seed/'.fake()->word().'/600/'.fake()->numberBetween(300, 700),
            'favicon_url' => "https://{$domain}/favicon.ico",
            'site_name' => $domain,
        ];
    }

    /**
     * Link with no extracted media, as produced by a failed metadata fetch.
     */
    public function withoutMedia(): static
    {
        return $this->state(fn (array $attributes): array => [
            'description' => null,
            'image_url' => null,
            'favicon_url' => null,
        ]);
    }
}
