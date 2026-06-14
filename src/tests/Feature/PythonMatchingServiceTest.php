<?php

namespace Tests\Feature;

use App\Models\JobPost;
use App\Models\PreferenceProfile;
use App\Services\PythonMatchingService;
use Tests\TestCase;

class PythonMatchingServiceTest extends TestCase
{
    public function test_python_matching_service_returns_score_and_reasons(): void
    {
        if (! $this->pythonIsAvailable()) {
            $this->markTestSkipped('python3 is not available in this environment.');
        }

        $profile = new PreferenceProfile([
            'desired_salary_min' => 700,
            'preferred_occupations' => ['営業'],
            'preferred_industries' => ['SaaS'],
            'preferred_locations' => ['東京'],
            'remote_required' => true,
            'preferred_remote_types' => ['フルリモート'],
            'preferred_technologies' => ['CRM'],
            'excluded_keywords' => ['飛び込み営業'],
        ]);

        $jobPost = new JobPost([
            'company_name' => 'High Match Co',
            'title' => 'SaaS 法人営業',
            'occupation' => '営業',
            'industry' => 'SaaS',
            'location' => '東京',
            'salary_min' => 800,
            'salary_max' => 1000,
            'remote_type' => 'フルリモート',
            'technologies' => ['CRM'],
            'memo' => '自社プロダクト',
        ]);

        $result = app(PythonMatchingService::class)->score($jobPost, $profile);

        $this->assertSame(100, $result['score']);
        $this->assertContains('スキル・経験一致: CRM', $result['reasons']);
    }

    private function pythonIsAvailable(): bool
    {
        $process = @proc_open(
            [(string) config('matching.python_binary'), '--version'],
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes,
        );

        if (! is_resource($process)) {
            return false;
        }

        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        return proc_close($process) === 0;
    }
}
