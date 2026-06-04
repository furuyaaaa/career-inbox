<?php

namespace Tests\Feature;

use App\Models\GmailImport;
use App\Models\GmailConnection;
use App\Models\JobPost;
use App\Services\GmailImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
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

    public function test_import_recent_extracts_job_fields_from_gmail_body(): void
    {
        $connection = GmailConnection::create([
            'email' => 'me@example.com',
            'access_token' => 'access-token',
            'token_expires_at' => now()->addHour(),
            'connected_at' => now(),
        ]);

        $body = implode("\n", [
            '会社名: Canvas AI',
            'ポジション: カスタマーサクセス',
            '勤務地: 東京',
            '想定年収: 700〜1000万円',
            '雇用形態: 正社員',
            '働き方: フルリモート',
            '業界: SaaS',
            'スキル: CRM、データ分析、顧客折衝',
            'https://example.com/jobs/canvas-ai',
        ]);

        Http::fake([
            'https://gmail.googleapis.com/gmail/v1/users/me/messages/message-1*' => Http::response([
                'id' => 'message-1',
                'snippet' => 'Canvas AI の求人紹介です。',
                'payload' => [
                    'headers' => [
                        ['name' => 'Subject', 'value' => '【求人紹介】Canvas AI カスタマーサクセス'],
                        ['name' => 'From', 'value' => 'agent@example.com'],
                        ['name' => 'Date', 'value' => 'Thu, 04 Jun 2026 12:00:00 +0900'],
                    ],
                    'mimeType' => 'text/plain',
                    'body' => ['data' => $this->gmailBody($body)],
                ],
            ], 200),
            'https://gmail.googleapis.com/gmail/v1/users/me/messages*' => Http::response([
                'messages' => [['id' => 'message-1']],
            ], 200),
        ]);

        $count = app(GmailImportService::class)->importRecent($connection, '求人', 1);

        $this->assertSame(1, $count);
        $this->assertDatabaseHas('job_posts', [
            'company_name' => 'Canvas AI',
            'title' => 'カスタマーサクセス',
            'occupation' => 'カスタマーサクセス',
            'industry' => 'IT',
            'location' => '東京',
            'salary_min' => 700,
            'salary_max' => 1000,
            'employment_type' => '正社員',
            'remote_type' => 'フルリモート',
            'url' => 'https://example.com/jobs/canvas-ai',
        ]);

        $jobPost = JobPost::where('company_name', 'Canvas AI')->firstOrFail();

        $this->assertSame(['CRM', 'データ分析', '顧客折衝'], $jobPost->technologies);
        $this->assertDatabaseHas('gmail_imports', [
            'gmail_connection_id' => $connection->id,
            'gmail_message_id' => 'message-1',
            'job_post_id' => $jobPost->id,
            'status' => 'imported',
        ]);
    }

    private function gmailBody(string $body): string
    {
        return rtrim(strtr(base64_encode($body), '+/', '-_'), '=');
    }
}
