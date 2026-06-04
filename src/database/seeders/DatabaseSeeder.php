<?php

namespace Database\Seeders;

use App\Models\JobPost;
use App\Models\PreferenceProfile;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::query()->updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => 'password',
            ],
        );

        JobPost::query()->firstOrCreate(
            [
                'company_name' => 'Atlas HR Tech',
                'title' => 'HR SaaS バックエンドエンジニア',
            ],
            [
                'source' => 'Gmail',
                'agent_name' => 'Career Agent',
                'occupation' => 'カスタマーサクセス',
                'industry' => 'SaaS',
                'location' => '東京',
                'salary_min' => 750,
                'salary_max' => 1100,
                'employment_type' => '正社員',
                'remote_type' => 'ハイブリッド',
                'technologies' => ['CRM', 'データ分析', '顧客折衝', '企画'],
                'status' => '応募したい',
                'interest_level' => 5,
                'url' => 'https://example.com/jobs/atlas-hr-tech',
                'received_at' => now()->toDateString(),
                'memo' => 'SaaS の顧客活用支援と改善提案に近い領域。',
            ],
        );

        JobPost::factory(6)->create();

        PreferenceProfile::primary()->update([
            'desired_salary_min' => 650,
            'preferred_occupations' => ['営業', 'マーケティング', 'カスタマーサクセス', 'エンジニア'],
            'preferred_industries' => ['IT', 'SaaS', '人材', '教育'],
            'preferred_locations' => ['東京', '全国'],
            'remote_required' => true,
            'preferred_remote_types' => ['フルリモート', 'ハイブリッド', '週3リモート'],
            'preferred_technologies' => ['法人営業', 'CRM', 'データ分析', '企画', 'Laravel', 'Python'],
            'excluded_keywords' => ['常駐のみ', '飛び込み営業'],
        ]);
    }
}
