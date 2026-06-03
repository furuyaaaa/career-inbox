<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PreferenceProfile>
 */
class PreferenceProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'desired_salary_min' => 650,
            'preferred_locations' => ['東京', '全国'],
            'remote_required' => true,
            'preferred_remote_types' => ['フルリモート', 'ハイブリッド'],
            'preferred_technologies' => ['Laravel', 'Python', 'AWS'],
            'excluded_keywords' => ['SES', '常駐のみ'],
        ];
    }
}
