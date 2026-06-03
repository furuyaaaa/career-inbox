<?php

namespace App\Services;

use App\Models\GmailConnection;
use App\Models\GmailImport;
use App\Models\JobPost;
use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class GmailImportService
{
    private const GMAIL_READONLY_SCOPE = 'https://www.googleapis.com/auth/gmail.readonly';
    private const USERINFO_EMAIL_SCOPE = 'https://www.googleapis.com/auth/userinfo.email';

    public function authorizationUrl(string $state): string
    {
        $this->assertConfigured();

        return 'https://accounts.google.com/o/oauth2/v2/auth?'.http_build_query([
            'client_id' => config('services.google.client_id'),
            'redirect_uri' => $this->redirectUri(),
            'response_type' => 'code',
            'scope' => implode(' ', [self::GMAIL_READONLY_SCOPE, self::USERINFO_EMAIL_SCOPE]),
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $state,
        ]);
    }

    public function exchangeCode(string $code): GmailConnection
    {
        $this->assertConfigured();

        $token = Http::asForm()
            ->post('https://oauth2.googleapis.com/token', [
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'redirect_uri' => $this->redirectUri(),
                'grant_type' => 'authorization_code',
                'code' => $code,
            ])
            ->throw()
            ->json();

        $email = $this->fetchEmail($token['access_token']);

        return GmailConnection::updateOrCreate(
            ['email' => $email],
            [
                'access_token' => $token['access_token'],
                'refresh_token' => $token['refresh_token'] ?? GmailConnection::where('email', $email)->value('refresh_token'),
                'token_expires_at' => now()->addSeconds((int) ($token['expires_in'] ?? 3600)),
                'scopes' => explode(' ', (string) ($token['scope'] ?? '')),
                'connected_at' => now(),
            ],
        );
    }

    public function importRecent(GmailConnection $connection, string $query, int $limit = 10): int
    {
        $token = $this->validAccessToken($connection);
        $messageIds = Http::withToken($token)
            ->get('https://gmail.googleapis.com/gmail/v1/users/me/messages', [
                'q' => $query,
                'maxResults' => min(max($limit, 1), 25),
            ])
            ->throw()
            ->json('messages', []);

        $count = 0;

        foreach ($messageIds as $message) {
            $messageId = $message['id'] ?? null;

            if (! $messageId || GmailImport::where('gmail_message_id', $messageId)->exists()) {
                continue;
            }

            $payload = Http::withToken($token)
                ->get("https://gmail.googleapis.com/gmail/v1/users/me/messages/{$messageId}?format=metadata&metadataHeaders=Subject&metadataHeaders=From&metadataHeaders=Date")
                ->throw()
                ->json();

            $jobPost = $this->jobPostFromMessage($payload);

            GmailImport::create([
                'gmail_connection_id' => $connection->id,
                'gmail_message_id' => $messageId,
                'subject' => $this->header($payload, 'Subject'),
                'sender' => $this->header($payload, 'From'),
                'snippet' => $payload['snippet'] ?? null,
                'received_at' => $jobPost->received_at,
                'job_post_id' => $jobPost->id,
                'status' => 'imported',
            ]);

            $count++;
        }

        return $count;
    }

    public function createDemoImports(): int
    {
        $samples = [
            [
                'message_id' => 'demo-gmail-sales-'.now()->format('YmdHis'),
                'company_name' => 'Hikari Cloud',
                'title' => '法人営業',
                'occupation' => '営業',
                'industry' => 'IT',
                'location' => '東京',
                'salary_min' => 650,
                'salary_max' => 900,
                'remote_type' => 'ハイブリッド',
                'technologies' => ['法人営業', 'CRM', '提案資料作成'],
                'subject' => '【求人紹介】Hikari Cloud 法人営業ポジション',
                'sender' => 'career-agent@example.com',
            ],
            [
                'message_id' => 'demo-gmail-marketing-'.now()->format('YmdHis'),
                'company_name' => 'Miraiz HR',
                'title' => '採用マーケティング',
                'occupation' => 'マーケティング',
                'industry' => '人材',
                'location' => '大阪',
                'salary_min' => 550,
                'salary_max' => 800,
                'remote_type' => '週3リモート',
                'technologies' => ['広告運用', 'CRM', 'データ分析'],
                'subject' => 'スカウト: 採用マーケティング担当のご案内',
                'sender' => 'scout@example.com',
            ],
            [
                'message_id' => 'demo-gmail-accounting-'.now()->format('YmdHis'),
                'company_name' => 'North Finance',
                'title' => '経理リーダー候補',
                'occupation' => '管理部門',
                'industry' => '金融',
                'location' => '福岡',
                'salary_min' => 600,
                'salary_max' => 850,
                'remote_type' => '出社中心',
                'technologies' => ['月次決算', '予実管理', 'チーム管理'],
                'subject' => '経理リーダー候補の求人について',
                'sender' => 'recruit@example.com',
            ],
        ];

        foreach ($samples as $sample) {
            $jobPost = JobPost::create([
                'company_name' => $sample['company_name'],
                'title' => $sample['title'],
                'occupation' => $sample['occupation'],
                'industry' => $sample['industry'],
                'source' => 'Gmail',
                'agent_name' => 'Gmail Demo',
                'location' => $sample['location'],
                'salary_min' => $sample['salary_min'],
                'salary_max' => $sample['salary_max'],
                'employment_type' => '正社員',
                'remote_type' => $sample['remote_type'],
                'technologies' => $sample['technologies'],
                'status' => '未確認',
                'interest_level' => 3,
                'received_at' => today(),
                'memo' => 'Gmail 取り込みデモから作成された求人です。',
            ]);

            GmailImport::create([
                'gmail_message_id' => $sample['message_id'],
                'subject' => $sample['subject'],
                'sender' => $sample['sender'],
                'snippet' => "{$sample['company_name']} / {$sample['title']} / {$sample['location']} / {$sample['salary_min']}万円から",
                'received_at' => now(),
                'job_post_id' => $jobPost->id,
                'status' => 'demo',
            ]);
        }

        return count($samples);
    }

    public function configured(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'));
    }

    private function validAccessToken(GmailConnection $connection): string
    {
        if ($connection->access_token && $connection->token_expires_at?->isFuture()) {
            return $connection->access_token;
        }

        if (! $connection->refresh_token) {
            throw new RuntimeException('Gmail の再認証が必要です。');
        }

        $token = Http::asForm()
            ->post('https://oauth2.googleapis.com/token', [
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'grant_type' => 'refresh_token',
                'refresh_token' => $connection->refresh_token,
            ])
            ->throw()
            ->json();

        $connection->update([
            'access_token' => $token['access_token'],
            'token_expires_at' => now()->addSeconds((int) ($token['expires_in'] ?? 3600)),
            'scopes' => explode(' ', (string) ($token['scope'] ?? implode(' ', $connection->scopes ?? []))),
        ]);

        return $connection->access_token;
    }

    private function jobPostFromMessage(array $payload): JobPost
    {
        $subject = $this->header($payload, 'Subject') ?: 'Gmail 取り込み求人';
        $sender = $this->header($payload, 'From');
        $snippet = $payload['snippet'] ?? '';
        $receivedAt = $this->dateFromMessage($payload);

        return JobPost::create([
            'company_name' => $this->guessCompanyName($subject, $sender),
            'title' => $subject,
            'source' => 'Gmail',
            'agent_name' => $sender,
            'status' => '未確認',
            'interest_level' => 3,
            'received_at' => $receivedAt,
            'memo' => $snippet,
        ]);
    }

    private function fetchEmail(string $accessToken): ?string
    {
        return Http::withToken($accessToken)
            ->get('https://www.googleapis.com/oauth2/v2/userinfo')
            ->throw()
            ->json('email');
    }

    private function header(array $payload, string $name): ?string
    {
        $headers = Arr::get($payload, 'payload.headers', []);

        foreach ($headers as $header) {
            if (strtolower((string) ($header['name'] ?? '')) === strtolower($name)) {
                return $header['value'] ?? null;
            }
        }

        return null;
    }

    private function dateFromMessage(array $payload): ?CarbonImmutable
    {
        $date = $this->header($payload, 'Date');

        try {
            return $date ? CarbonImmutable::parse($date) : null;
        } catch (Throwable) {
            return null;
        }
    }

    private function guessCompanyName(string $subject, ?string $sender): string
    {
        if (preg_match('/【求人紹介】(.+?)\s/u', $subject, $matches)) {
            return $matches[1];
        }

        return $sender ? mb_substr($sender, 0, 80) : 'Gmail 取り込み';
    }

    private function redirectUri(): string
    {
        return config('services.google.redirect') ?: url('/gmail/callback');
    }

    private function assertConfigured(): void
    {
        if (! $this->configured()) {
            throw new RuntimeException('Google OAuth の認証情報が未設定です。');
        }
    }
}
