<?php

namespace App\Services;

use App\Models\JobPost;
use App\Models\PreferenceProfile;
use RuntimeException;

class PythonMatchingService implements MatchingService
{
    /**
     * @return array{score: int, reasons: array<int, string>}
     */
    public function score(JobPost $jobPost, PreferenceProfile $profile): array
    {
        $payload = json_encode($this->payload($jobPost, $profile), JSON_UNESCAPED_UNICODE);

        if ($payload === false) {
            throw new RuntimeException('マッチング入力のJSON変換に失敗しました。');
        }

        $output = $this->runPython($payload);
        $result = json_decode($output, true);

        if (! is_array($result) || ! isset($result['score'], $result['reasons']) || ! is_array($result['reasons'])) {
            throw new RuntimeException('Pythonマッチングの戻り値が不正です。');
        }

        return [
            'score' => max(0, min(100, (int) $result['score'])),
            'reasons' => collect($result['reasons'])->map(fn (mixed $reason): string => (string) $reason)->values()->all(),
        ];
    }

    private function runPython(string $payload): string
    {
        $process = @proc_open(
            [
                (string) config('matching.python_binary'),
                (string) config('matching.python_script'),
            ],
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes,
            base_path(),
        );

        if (! is_resource($process)) {
            throw new RuntimeException('Pythonマッチングプロセスを起動できませんでした。');
        }

        fwrite($pipes[0], $payload);
        fclose($pipes[0]);

        $output = stream_get_contents($pipes[1]);
        $error = stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        if ($exitCode !== 0) {
            throw new RuntimeException('Pythonマッチングに失敗しました: '.trim((string) $error));
        }

        return (string) $output;
    }

    private function payload(JobPost $jobPost, PreferenceProfile $profile): array
    {
        return [
            'job' => [
                'company_name' => $jobPost->company_name,
                'title' => $jobPost->title,
                'occupation' => $jobPost->occupation,
                'industry' => $jobPost->industry,
                'source' => $jobPost->source,
                'agent_name' => $jobPost->agent_name,
                'location' => $jobPost->location,
                'salary_min' => $jobPost->salary_min,
                'salary_max' => $jobPost->salary_max,
                'employment_type' => $jobPost->employment_type,
                'remote_type' => $jobPost->remote_type,
                'technologies' => $jobPost->technologies ?? [],
                'memo' => $jobPost->memo,
            ],
            'profile' => [
                'desired_salary_min' => $profile->desired_salary_min,
                'preferred_occupations' => $profile->preferred_occupations ?? [],
                'preferred_industries' => $profile->preferred_industries ?? [],
                'preferred_locations' => $profile->preferred_locations ?? [],
                'remote_required' => $profile->remote_required,
                'preferred_remote_types' => $profile->preferred_remote_types ?? [],
                'preferred_technologies' => $profile->preferred_technologies ?? [],
                'excluded_keywords' => $profile->excluded_keywords ?? [],
            ],
        ];
    }
}
