<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobPost>
 */
class JobPostFactory extends Factory
{
    public function definition(): array
    {
        $salaryMin = fake()->numberBetween(450, 900);

        return [
            'company_name' => fake()->company(),
            'title' => fake()->randomElement([
                '法人営業マネージャー',
                'マーケティングプランナー',
                'カスタマーサクセス',
                '経理リーダー',
                '人事採用担当',
                'Laravel バックエンドエンジニア',
            ]),
            'occupation' => fake()->randomElement(['営業', 'マーケティング', 'カスタマーサクセス', '経理', '人事', 'エンジニア']),
            'industry' => fake()->randomElement(['IT', '人材', 'メーカー', '医療', '教育', 'SaaS']),
            'source' => fake()->randomElement(['Gmail', 'Green', 'ビズリーチ', 'Wantedly', 'エージェント']),
            'agent_name' => fake()->optional()->name(),
            'location' => fake()->randomElement(['東京', '大阪', '福岡', '全国']),
            'salary_min' => $salaryMin,
            'salary_max' => $salaryMin + fake()->numberBetween(100, 350),
            'employment_type' => fake()->randomElement(['正社員', '契約社員', '業務委託']),
            'remote_type' => fake()->randomElement(['フルリモート', 'ハイブリッド', '週3リモート', '出社中心']),
            'technologies' => fake()->randomElements(['法人営業', 'CRM', 'データ分析', '採用', '経理', '英語', 'Laravel', 'Python'], 3),
            'status' => fake()->randomElement(['未確認', '気になる', '応募したい', '応募済み', '見送り']),
            'interest_level' => fake()->numberBetween(1, 5),
            'url' => fake()->url(),
            'received_at' => fake()->dateTimeBetween('-30 days'),
            'memo' => fake()->optional()->sentence(),
        ];
    }
}
