<?php

namespace Tests\Feature;

use App\Models\JobPost;
use App\Models\PreferenceProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_user_scoped_summary_and_top_jobs(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        PreferenceProfile::primary($user->id)->update([
            'desired_salary_min' => 700,
            'preferred_occupations' => ['営業'],
            'preferred_industries' => ['SaaS'],
            'preferred_locations' => ['東京'],
            'remote_required' => true,
            'preferred_remote_types' => ['フルリモート'],
            'preferred_technologies' => ['CRM'],
        ]);

        JobPost::factory()->create([
            'user_id' => $user->id,
            'company_name' => 'High Match Co',
            'title' => 'SaaS 法人営業',
            'occupation' => '営業',
            'industry' => 'SaaS',
            'location' => '東京',
            'salary_min' => 800,
            'salary_max' => 1000,
            'remote_type' => 'フルリモート',
            'technologies' => ['CRM'],
            'status' => '応募したい',
            'source' => 'Gmail',
            'received_at' => '2026-06-10',
        ]);

        JobPost::factory()->create([
            'user_id' => $user->id,
            'company_name' => 'Low Match Co',
            'title' => '店舗スタッフ',
            'occupation' => '販売',
            'industry' => '小売',
            'location' => '大阪',
            'salary_min' => 450,
            'salary_max' => 550,
            'remote_type' => '出社中心',
            'technologies' => ['接客'],
            'status' => '未確認',
            'source' => 'エージェント',
            'received_at' => '2026-06-11',
        ]);

        JobPost::factory()->create([
            'user_id' => $otherUser->id,
            'company_name' => 'Other User Co',
            'title' => '非表示求人',
            'status' => '応募したい',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response
            ->assertOk()
            ->assertSee('ダッシュボード')
            ->assertSee('2件')
            ->assertSee('応募したい 1件')
            ->assertSee('未確認 1件')
            ->assertSeeInOrder(['High Match Co', 'Low Match Co'])
            ->assertSee('最近のGmail求人')
            ->assertSee('SaaS 法人営業')
            ->assertDontSee('Other User Co');
    }
}
