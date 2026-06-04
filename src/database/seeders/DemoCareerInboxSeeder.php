<?php

namespace Database\Seeders;

use App\Models\GmailConnection;
use App\Models\GmailImport;
use App\Models\JobPost;
use App\Models\PreferenceProfile;
use Illuminate\Database\Seeder;

class DemoCareerInboxSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPreferenceProfile();
        $connection = $this->seedGmailConnection();

        foreach ($this->jobPosts() as $jobPostData) {
            $jobPost = JobPost::query()->updateOrCreate(
                [
                    'company_name' => $jobPostData['company_name'],
                    'title' => $jobPostData['title'],
                ],
                collect($jobPostData)->except(['gmail_message_id', 'subject', 'sender', 'snippet'])->all(),
            );

            if (($jobPostData['source'] ?? null) === 'Gmail') {
                GmailImport::query()->updateOrCreate(
                    ['gmail_message_id' => $jobPostData['gmail_message_id']],
                    [
                        'gmail_connection_id' => $connection->id,
                        'subject' => $jobPostData['subject'],
                        'sender' => $jobPostData['sender'],
                        'snippet' => $jobPostData['snippet'],
                        'received_at' => $jobPost->received_at,
                        'job_post_id' => $jobPost->id,
                        'status' => 'demo',
                    ],
                );
            }
        }
    }

    private function seedPreferenceProfile(): void
    {
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

    private function seedGmailConnection(): GmailConnection
    {
        return GmailConnection::query()->updateOrCreate(
            ['email' => 'demo.gmail@example.com'],
            [
                'access_token' => 'demo-access-token',
                'refresh_token' => 'demo-refresh-token',
                'token_expires_at' => now()->addHour(),
                'scopes' => ['https://www.googleapis.com/auth/gmail.readonly'],
                'connected_at' => now(),
            ],
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function jobPosts(): array
    {
        return [
            [
                'company_name' => 'Atlas HR Tech',
                'title' => 'カスタマーサクセス',
                'occupation' => 'カスタマーサクセス',
                'industry' => 'SaaS',
                'source' => 'Gmail',
                'agent_name' => 'Career Agent',
                'location' => '東京',
                'salary_min' => 750,
                'salary_max' => 1100,
                'employment_type' => '正社員',
                'remote_type' => 'ハイブリッド',
                'technologies' => ['CRM', 'データ分析', '顧客折衝', '企画'],
                'status' => '応募したい',
                'interest_level' => 5,
                'url' => 'https://example.com/jobs/atlas-hr-tech',
                'received_at' => now()->subDays(1)->toDateString(),
                'memo' => 'Gmail本文から取り込んだ想定の求人。SaaS の顧客活用支援と改善提案に近い領域。',
                'gmail_message_id' => 'demo-atlas-hr-tech',
                'subject' => '【求人紹介】Atlas HR Tech カスタマーサクセス',
                'sender' => 'career-agent@example.com',
                'snippet' => 'SaaS企業のカスタマーサクセス求人です。年収750万円から。',
            ],
            [
                'company_name' => 'Hikari Cloud',
                'title' => '法人営業',
                'occupation' => '営業',
                'industry' => 'IT',
                'source' => 'Gmail',
                'agent_name' => 'Scout Mail',
                'location' => '東京',
                'salary_min' => 650,
                'salary_max' => 900,
                'employment_type' => '正社員',
                'remote_type' => 'ハイブリッド',
                'technologies' => ['法人営業', 'CRM', '提案資料作成'],
                'status' => '気になる',
                'interest_level' => 4,
                'url' => 'https://example.com/jobs/hikari-cloud',
                'received_at' => now()->subDays(2)->toDateString(),
                'memo' => 'クラウドサービスの提案営業。既存顧客向けの深耕営業が中心。',
                'gmail_message_id' => 'demo-hikari-cloud-sales',
                'subject' => 'スカウト: Hikari Cloud 法人営業',
                'sender' => 'scout@example.com',
                'snippet' => '法人営業経験を活かせるクラウドサービスの求人です。',
            ],
            [
                'company_name' => 'Miraiz HR',
                'title' => '採用マーケティング',
                'occupation' => 'マーケティング',
                'industry' => '人材',
                'source' => 'Gmail',
                'agent_name' => 'Recruit Agent',
                'location' => '大阪',
                'salary_min' => 550,
                'salary_max' => 800,
                'employment_type' => '正社員',
                'remote_type' => '週3リモート',
                'technologies' => ['広告運用', 'CRM', 'データ分析'],
                'status' => '未確認',
                'interest_level' => 3,
                'url' => 'https://example.com/jobs/miraiz-hr',
                'received_at' => now()->subDays(3)->toDateString(),
                'memo' => '採用広報、広告運用、候補者体験の改善を担当。',
                'gmail_message_id' => 'demo-miraiz-hr-marketing',
                'subject' => '求人紹介: Miraiz HR 採用マーケティング',
                'sender' => 'recruit@example.com',
                'snippet' => '採用マーケティング担当の求人をご紹介します。',
            ],
            [
                'company_name' => 'North Finance',
                'title' => '経理リーダー候補',
                'occupation' => '管理部門',
                'industry' => '金融',
                'source' => 'ビズリーチ',
                'agent_name' => 'Direct Recruiter',
                'location' => '福岡',
                'salary_min' => 600,
                'salary_max' => 850,
                'employment_type' => '正社員',
                'remote_type' => '出社中心',
                'technologies' => ['月次決算', '予実管理', 'チーム管理'],
                'status' => '未確認',
                'interest_level' => 2,
                'url' => 'https://example.com/jobs/north-finance',
                'received_at' => now()->subDays(4)->toDateString(),
                'memo' => '経理チームのリーダー候補。月次決算と予実管理が中心。',
            ],
            [
                'company_name' => 'Canvas AI',
                'title' => 'Laravel バックエンドエンジニア',
                'occupation' => 'エンジニア',
                'industry' => 'IT',
                'source' => 'Green',
                'agent_name' => null,
                'location' => '全国',
                'salary_min' => 800,
                'salary_max' => 1200,
                'employment_type' => '正社員',
                'remote_type' => 'フルリモート',
                'technologies' => ['Laravel', 'PHP', 'AWS', 'Python'],
                'status' => '応募したい',
                'interest_level' => 5,
                'url' => 'https://example.com/jobs/canvas-ai',
                'received_at' => now()->subDays(5)->toDateString(),
                'memo' => 'AI関連プロダクトのバックエンド開発。フルリモート可。',
            ],
        ];
    }
}
