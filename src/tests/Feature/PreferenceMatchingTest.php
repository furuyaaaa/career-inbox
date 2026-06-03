<?php

namespace Tests\Feature;

use App\Models\JobPost;
use App\Models\PreferenceProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PreferenceMatchingTest extends TestCase
{
    use RefreshDatabase;

    public function test_preferences_can_be_updated(): void
    {
        PreferenceProfile::primary();

        $response = $this->withSession(['_token' => 'test-token'])->put('/preferences', [
            '_token' => 'test-token',
            'desired_salary_min' => 800,
            'remote_required' => 1,
            'preferred_occupations_text' => '営業, マーケティング',
            'preferred_industries_text' => 'IT, SaaS',
            'preferred_locations_text' => '東京, 全国',
            'preferred_remote_types_text' => 'フルリモート, ハイブリッド',
            'preferred_technologies_text' => '法人営業, CRM, データ分析',
            'excluded_keywords_text' => 'SES, 常駐のみ',
        ]);

        $response->assertRedirect('/preferences');

        $profile = PreferenceProfile::primary();

        $this->assertSame(800, $profile->desired_salary_min);
        $this->assertSame(['営業', 'マーケティング'], $profile->preferred_occupations);
        $this->assertSame(['IT', 'SaaS'], $profile->preferred_industries);
        $this->assertSame(['法人営業', 'CRM', 'データ分析'], $profile->preferred_technologies);
        $this->assertSame(['SES', '常駐のみ'], $profile->excluded_keywords);
    }

    public function test_jobs_can_be_sorted_by_matching_score(): void
    {
        PreferenceProfile::primary()->update([
            'desired_salary_min' => 700,
            'preferred_occupations' => ['営業', 'マーケティング'],
            'preferred_industries' => ['IT', 'SaaS'],
            'preferred_locations' => ['東京'],
            'remote_required' => true,
            'preferred_remote_types' => ['フルリモート'],
            'preferred_technologies' => ['法人営業', 'CRM', 'データ分析'],
            'excluded_keywords' => ['飛び込み営業'],
        ]);

        JobPost::factory()->create([
            'company_name' => 'Low Match Co',
            'title' => '店舗スタッフ',
            'occupation' => '販売',
            'industry' => '小売',
            'location' => '大阪',
            'salary_min' => 450,
            'salary_max' => 600,
            'remote_type' => '出社中心',
            'technologies' => ['接客'],
            'memo' => '飛び込み営業あり',
            'received_at' => '2026-06-03',
        ]);

        JobPost::factory()->create([
            'company_name' => 'High Match Co',
            'title' => 'SaaS 法人営業',
            'occupation' => '営業',
            'industry' => 'SaaS',
            'location' => '東京',
            'salary_min' => 800,
            'salary_max' => 1100,
            'remote_type' => 'フルリモート',
            'technologies' => ['法人営業', 'CRM', 'データ分析'],
            'memo' => '自社プロダクト',
            'received_at' => '2026-06-02',
        ]);

        $response = $this->get('/jobs?sort=match');

        $response
            ->assertOk()
            ->assertSeeInOrder(['High Match Co', 'Low Match Co'])
            ->assertSee('スキル・経験一致: 法人営業, CRM, データ分析');
    }
}
