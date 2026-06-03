<?php

namespace Tests\Feature;

use App\Models\GmailImport;
use App\Models\JobPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GmailIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_gmail_page_loads(): void
    {
        $response = $this->get('/gmail');

        $response
            ->assertOk()
            ->assertSee('Gmail 連携')
            ->assertSee('デモ取り込み');
    }

    public function test_demo_import_creates_job_posts_from_gmail(): void
    {
        $response = $this->withSession(['_token' => 'test-token'])
            ->post('/gmail/demo-import', ['_token' => 'test-token']);

        $response->assertRedirect('/gmail');

        $this->assertSame(3, JobPost::where('source', 'Gmail')->count());
        $this->assertSame(3, GmailImport::where('status', 'demo')->count());
        $this->assertDatabaseHas('job_posts', [
            'company_name' => 'Hikari Cloud',
            'occupation' => '営業',
            'industry' => 'IT',
        ]);
    }

    public function test_connect_without_google_credentials_returns_to_settings(): void
    {
        config([
            'services.google.client_id' => null,
            'services.google.client_secret' => null,
        ]);

        $response = $this->get('/gmail/connect');

        $response
            ->assertRedirect('/gmail')
            ->assertSessionHas('status', 'Google OAuth の認証情報が未設定です。');
    }
}
