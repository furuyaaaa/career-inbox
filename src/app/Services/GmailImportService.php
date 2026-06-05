<?php

namespace App\Services;

use App\Models\GmailConnection;
use App\Models\GmailImport;
use App\Models\GmailOauthSetting;
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
            'client_id' => $this->clientId(),
            'redirect_uri' => $this->redirectUri(),
            'response_type' => 'code',
            'scope' => implode(' ', [self::GMAIL_READONLY_SCOPE, self::USERINFO_EMAIL_SCOPE]),
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $state,
        ]);
    }

    public function exchangeCode(string $code, int $userId): GmailConnection
    {
        $this->assertConfigured();

        $token = Http::asForm()
            ->post('https://oauth2.googleapis.com/token', [
                'client_id' => $this->clientId(),
                'client_secret' => $this->clientSecret(),
                'redirect_uri' => $this->redirectUri(),
                'grant_type' => 'authorization_code',
                'code' => $code,
            ])
            ->throw()
            ->json();

        $email = $this->fetchEmail($token['access_token']);

        return GmailConnection::updateOrCreate(
            [
                'user_id' => $userId,
                'email' => $email,
            ],
            [
                'user_id' => $userId,
                'access_token' => $token['access_token'],
                'refresh_token' => $token['refresh_token'] ?? GmailConnection::where('user_id', $userId)->where('email', $email)->value('refresh_token'),
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

            if (! $messageId || GmailImport::where('user_id', $connection->user_id)->where('gmail_message_id', $messageId)->exists()) {
                continue;
            }

            $payload = Http::withToken($token)
                ->get("https://gmail.googleapis.com/gmail/v1/users/me/messages/{$messageId}?format=full")
                ->throw()
                ->json();

            $jobPost = $this->jobPostFromMessage($payload, $connection);

            GmailImport::create([
                'user_id' => $connection->user_id,
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

    public function createDemoImports(int $userId): int
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
                'user_id' => $userId,
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
                'user_id' => $userId,
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
        return filled($this->clientId()) && filled($this->clientSecret());
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
                'client_id' => $this->clientId(),
                'client_secret' => $this->clientSecret(),
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

    private function jobPostFromMessage(array $payload, GmailConnection $connection): JobPost
    {
        $subject = $this->header($payload, 'Subject') ?: 'Gmail 取り込み求人';
        $sender = $this->header($payload, 'From');
        $snippet = $payload['snippet'] ?? '';
        $receivedAt = $this->dateFromMessage($payload);
        $body = $this->messageBody($payload);
        $fields = $this->extractJobFields($subject, $body);

        return JobPost::create([
            'user_id' => $connection->user_id,
            'company_name' => $fields['company_name'] ?? $this->guessCompanyName($subject, $sender),
            'title' => $fields['title'] ?? $subject,
            'occupation' => $fields['occupation'] ?? null,
            'industry' => $fields['industry'] ?? null,
            'source' => 'Gmail',
            'agent_name' => $sender,
            'location' => $fields['location'] ?? null,
            'salary_min' => $fields['salary_min'] ?? null,
            'salary_max' => $fields['salary_max'] ?? null,
            'employment_type' => $fields['employment_type'] ?? null,
            'remote_type' => $fields['remote_type'] ?? null,
            'technologies' => $fields['technologies'] ?? [],
            'status' => '未確認',
            'interest_level' => 3,
            'url' => $fields['url'] ?? null,
            'received_at' => $receivedAt,
            'memo' => trim($snippet."\n\n".$body),
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

    private function messageBody(array $payload): string
    {
        $plain = $this->bodyPart($payload['payload'] ?? [], 'text/plain');
        $html = $this->bodyPart($payload['payload'] ?? [], 'text/html');
        $body = $plain ?: strip_tags((string) $html);

        return trim(html_entity_decode($body, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    private function bodyPart(array $part, string $mimeType): ?string
    {
        if (($part['mimeType'] ?? null) === $mimeType && filled(Arr::get($part, 'body.data'))) {
            return $this->decodeGmailBody((string) Arr::get($part, 'body.data'));
        }

        foreach ($part['parts'] ?? [] as $child) {
            $body = $this->bodyPart($child, $mimeType);

            if ($body !== null) {
                return $body;
            }
        }

        return null;
    }

    private function decodeGmailBody(string $data): string
    {
        $normalized = strtr($data, '-_', '+/');
        $normalized .= str_repeat('=', (4 - strlen($normalized) % 4) % 4);

        return (string) base64_decode($normalized);
    }

    /**
     * @return array{
     *     company_name?: string,
     *     title?: string,
     *     occupation?: string,
     *     industry?: string,
     *     location?: string,
     *     salary_min?: int,
     *     salary_max?: int,
     *     employment_type?: string,
     *     remote_type?: string,
     *     technologies?: array<int, string>,
     *     url?: string
     * }
     */
    private function extractJobFields(string $subject, string $body): array
    {
        $text = trim($subject."\n".$body);
        $fields = [];

        $fields['company_name'] = $this->lineValue($text, ['会社名', '企業名', '社名']);
        $fields['title'] = $this->lineValue($text, ['求人名', 'ポジション', '募集職種']);
        $fields['location'] = $this->lineValue($text, ['勤務地', '勤務場所']);
        $fields['employment_type'] = $this->detectFirst($text, ['正社員', '契約社員', '業務委託', '副業']);
        $fields['remote_type'] = $this->detectRemoteType($text);
        $fields['occupation'] = $this->detectOccupation($text);
        $fields['industry'] = $this->detectIndustry($text);
        $fields['technologies'] = $this->extractSkills($text);
        $fields['url'] = $this->extractUrl($text);

        if (preg_match('/(?:年収|想定年収|給与)[^\d]*(\d{3,4})\s*(?:万|万円)?\s*(?:-|~|〜|～|から|以上)?\s*(\d{3,4})?/u', $text, $matches)) {
            $fields['salary_min'] = (int) $matches[1];
            $fields['salary_max'] = isset($matches[2]) && $matches[2] !== '' ? (int) $matches[2] : null;
        }

        return array_filter($fields, fn ($value): bool => $value !== null && $value !== [] && $value !== '');
    }

    /**
     * @param array<int, string> $labels
     */
    private function lineValue(string $text, array $labels): ?string
    {
        $labelPattern = implode('|', array_map(fn (string $label): string => preg_quote($label, '/'), $labels));

        if (preg_match("/(?:{$labelPattern})\s*[:：]\s*(.+)/u", $text, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    /**
     * @param array<int, string> $values
     */
    private function detectFirst(string $text, array $values): ?string
    {
        foreach ($values as $value) {
            if (str_contains($text, $value)) {
                return $value;
            }
        }

        return null;
    }

    private function detectRemoteType(string $text): ?string
    {
        if (str_contains($text, 'フルリモート') || str_contains($text, '完全在宅')) {
            return 'フルリモート';
        }

        if (str_contains($text, 'ハイブリッド')) {
            return 'ハイブリッド';
        }

        if (preg_match('/週\s*3.*リモート/u', $text)) {
            return '週3リモート';
        }

        if (str_contains($text, '出社中心')) {
            return '出社中心';
        }

        return null;
    }

    private function detectOccupation(string $text): ?string
    {
        $keywords = [
            '営業' => ['営業', 'セールス', 'アカウント'],
            'マーケティング' => ['マーケティング', '広告運用', 'SEO'],
            'カスタマーサクセス' => ['カスタマーサクセス', 'CS', '導入支援'],
            '管理部門' => ['経理', '人事', '総務', '法務', '採用'],
            '企画' => ['事業企画', '営業企画', '商品企画'],
            'エンジニア' => ['エンジニア', '開発', 'Laravel', 'PHP', 'React'],
            'デザイン' => ['デザイナー', 'UI', 'UX'],
        ];

        foreach ($keywords as $occupation => $needles) {
            if ($this->detectFirst($text, $needles)) {
                return $occupation;
            }
        }

        return null;
    }

    private function detectIndustry(string $text): ?string
    {
        $keywords = [
            'IT' => ['IT', 'SaaS', 'クラウド', 'ソフトウェア'],
            '人材' => ['人材', '採用', 'HR'],
            '金融' => ['金融', 'FinTech', '保険', '証券'],
            '教育' => ['教育', 'EdTech', '研修'],
            '医療' => ['医療', 'ヘルスケア'],
            '小売' => ['小売', 'EC', 'D2C'],
            'メーカー' => ['メーカー', '製造'],
        ];

        foreach ($keywords as $industry => $needles) {
            if ($this->detectFirst($text, $needles)) {
                return $industry;
            }
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    private function extractSkills(string $text): array
    {
        $line = $this->lineValue($text, ['スキル', '経験', '必須条件', '歓迎条件']);

        if (! $line) {
            return [];
        }

        return collect(preg_split('/[,、\/・]/u', $line) ?: [])
            ->map(fn (string $skill): string => trim($skill))
            ->filter()
            ->take(8)
            ->values()
            ->all();
    }

    private function extractUrl(string $text): ?string
    {
        if (preg_match('/https?:\/\/[^\s<>"\']+/u', $text, $matches)) {
            return rtrim($matches[0], '。、)');
        }

        return null;
    }

    private function redirectUri(): string
    {
        return GmailOauthSetting::current()->redirect_uri ?: config('services.google.redirect') ?: url('/gmail/callback');
    }

    private function clientId(): ?string
    {
        return GmailOauthSetting::current()->client_id ?: config('services.google.client_id');
    }

    private function clientSecret(): ?string
    {
        return GmailOauthSetting::current()->client_secret ?: config('services.google.client_secret');
    }

    private function assertConfigured(): void
    {
        if (! $this->configured()) {
            throw new RuntimeException('Google OAuth の認証情報が未設定です。');
        }
    }
}
