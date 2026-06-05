<?php

namespace Tests\Feature;

use App\Models\JobPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobPostCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_job_posts_can_be_listed(): void
    {
        JobPost::factory()->create([
            'user_id' => $this->user->id,
            'company_name' => 'Hikari Cloud',
            'title' => '法人営業',
            'occupation' => '営業',
            'industry' => 'IT',
        ]);

        $response = $this->get('/jobs');

        $response
            ->assertOk()
            ->assertSee('Hikari Cloud')
            ->assertSee('法人営業');
    }

    public function test_job_posts_are_scoped_to_authenticated_user(): void
    {
        JobPost::factory()->create([
            'user_id' => $this->user->id,
            'company_name' => 'My Company',
        ]);
        JobPost::factory()->create([
            'user_id' => User::factory()->create()->id,
            'company_name' => 'Other Company',
        ]);

        $response = $this->get('/jobs');

        $response
            ->assertOk()
            ->assertSee('My Company')
            ->assertDontSee('Other Company');
    }

    public function test_job_post_can_be_created(): void
    {
        $response = $this->withSession(['_token' => 'test-token'])->post('/jobs', [
            '_token' => 'test-token',
            'company_name' => 'Canvas AI',
            'title' => 'カスタマーサクセス',
            'occupation' => 'カスタマーサクセス',
            'industry' => 'SaaS',
            'source' => 'Gmail',
            'location' => '東京',
            'salary_min' => 700,
            'salary_max' => 1000,
            'employment_type' => '正社員',
            'remote_type' => 'フルリモート',
            'technologies_text' => 'CRM, データ分析, 顧客折衝',
            'status' => '気になる',
            'interest_level' => 4,
            'url' => 'https://example.com/jobs/canvas-ai',
            'received_at' => '2026-06-04',
            'memo' => '顧客活用支援に近い職種。',
        ]);

        $response->assertRedirect('/jobs');

        $this->assertDatabaseHas('job_posts', [
            'user_id' => $this->user->id,
            'company_name' => 'Canvas AI',
            'title' => 'カスタマーサクセス',
            'status' => '気になる',
        ]);

        $this->assertSame(
            ['CRM', 'データ分析', '顧客折衝'],
            JobPost::where('company_name', 'Canvas AI')->firstOrFail()->technologies,
        );
    }

    public function test_job_post_can_be_updated(): void
    {
        $jobPost = JobPost::factory()->create([
            'user_id' => $this->user->id,
            'status' => '未確認',
        ]);

        $response = $this->withSession(['_token' => 'test-token'])->put("/jobs/{$jobPost->id}", [
            '_token' => 'test-token',
            'company_name' => $jobPost->company_name,
            'title' => $jobPost->title,
            'occupation' => 'マーケティング',
            'industry' => '人材',
            'source' => $jobPost->source,
            'location' => $jobPost->location,
            'salary_min' => 650,
            'salary_max' => 950,
            'employment_type' => '正社員',
            'remote_type' => 'ハイブリッド',
            'technologies_text' => '広告運用, CRM',
            'status' => '応募したい',
            'interest_level' => 5,
        ]);

        $response->assertRedirect("/jobs/{$jobPost->id}");

        $this->assertDatabaseHas('job_posts', [
            'id' => $jobPost->id,
            'status' => '応募したい',
            'interest_level' => 5,
        ]);
    }

    public function test_job_post_can_be_deleted(): void
    {
        $jobPost = JobPost::factory()->create();
        $jobPost->update(['user_id' => $this->user->id]);

        $response = $this->withSession(['_token' => 'test-token'])->delete("/jobs/{$jobPost->id}", [
            '_token' => 'test-token',
        ]);

        $response->assertRedirect('/jobs');
        $this->assertDatabaseMissing('job_posts', ['id' => $jobPost->id]);
    }
}
