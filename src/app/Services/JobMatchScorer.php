<?php

namespace App\Services;

use App\Models\JobPost;
use App\Models\PreferenceProfile;
use Illuminate\Support\Str;

class JobMatchScorer
{
    public function score(JobPost $jobPost, PreferenceProfile $profile): array
    {
        $score = 20;
        $reasons = [];
        $excluded = $this->matchedExcludedKeywords($jobPost, $profile);

        if ($this->occupationMatches($jobPost, $profile)) {
            $score += 16;
            $reasons[] = '職種カテゴリが希望に合っています';
        }

        if ($this->industryMatches($jobPost, $profile)) {
            $score += 10;
            $reasons[] = '業界が希望に合っています';
        }

        if ($profile->desired_salary_min && $jobPost->salary_min) {
            if ($jobPost->salary_min >= $profile->desired_salary_min) {
                $score += 24;
                $reasons[] = "年収下限 {$jobPost->salary_min}万円が希望を満たしています";
            } elseif ($jobPost->salary_max && $jobPost->salary_max >= $profile->desired_salary_min) {
                $score += 12;
                $reasons[] = '年収レンジ内に希望額が入っています';
            }
        }

        if ($this->locationMatches($jobPost, $profile)) {
            $score += 18;
            $reasons[] = '勤務地が希望条件に合っています';
        }

        if ($this->remoteMatches($jobPost, $profile)) {
            $score += 18;
            $reasons[] = 'リモート条件が合っています';
        } elseif ($profile->remote_required) {
            $score -= 12;
            $reasons[] = 'リモート条件は要確認です';
        }

        $matchedSkills = $this->matchedSkills($jobPost, $profile);
        if ($matchedSkills !== []) {
            $score += min(count($matchedSkills) * 8, 24);
            $reasons[] = 'スキル・経験一致: '.implode(', ', $matchedSkills);
        }

        if ($excluded !== []) {
            $score -= 28;
            $reasons[] = '除外候補: '.implode(', ', $excluded);
        }

        $score = max(0, min(100, $score));

        return [
            'score' => $score,
            'reasons' => $reasons ?: ['条件との一致が少ないため、後で確認でよさそうです'],
        ];
    }

    private function locationMatches(JobPost $jobPost, PreferenceProfile $profile): bool
    {
        $locations = $profile->preferred_locations ?? [];

        if ($locations === [] || ! $jobPost->location) {
            return false;
        }

        return in_array($jobPost->location, $locations, true)
            || in_array('全国', $locations, true)
            || $jobPost->location === '全国';
    }

    private function occupationMatches(JobPost $jobPost, PreferenceProfile $profile): bool
    {
        return $this->containsAny($jobPost->occupation, $profile->preferred_occupations ?? []);
    }

    private function industryMatches(JobPost $jobPost, PreferenceProfile $profile): bool
    {
        return $this->containsAny($jobPost->industry, $profile->preferred_industries ?? []);
    }

    private function remoteMatches(JobPost $jobPost, PreferenceProfile $profile): bool
    {
        $remoteTypes = $profile->preferred_remote_types ?? [];

        if ($remoteTypes === [] || ! $jobPost->remote_type) {
            return false;
        }

        return in_array($jobPost->remote_type, $remoteTypes, true);
    }

    private function matchedSkills(JobPost $jobPost, PreferenceProfile $profile): array
    {
        $jobSkills = collect($jobPost->technologies ?? [])
            ->map(fn (string $skill): string => Str::lower($skill));

        return collect($profile->preferred_technologies ?? [])
            ->filter(function (string $skill) use ($jobPost, $jobSkills): bool {
                $normalizedSkill = Str::lower($skill);

                return $jobSkills->contains($normalizedSkill)
                    || str_contains(Str::lower($jobPost->title), $normalizedSkill)
                    || str_contains(Str::lower($jobPost->memo ?? ''), $normalizedSkill);
            })
            ->values()
            ->all();
    }

    private function matchedExcludedKeywords(JobPost $jobPost, PreferenceProfile $profile): array
    {
        $text = Str::lower(implode(' ', [
            $jobPost->company_name,
            $jobPost->title,
            $jobPost->occupation,
            $jobPost->industry,
            $jobPost->source,
            $jobPost->agent_name,
            $jobPost->location,
            $jobPost->employment_type,
            $jobPost->remote_type,
            implode(' ', $jobPost->technologies ?? []),
            $jobPost->memo,
        ]));

        return collect($profile->excluded_keywords ?? [])
            ->filter(fn (string $keyword): bool => $keyword !== '' && str_contains($text, Str::lower($keyword)))
            ->values()
            ->all();
    }

    /**
     * @param array<int, string> $needles
     */
    private function containsAny(?string $haystack, array $needles): bool
    {
        if (! $haystack || $needles === []) {
            return false;
        }

        $normalizedHaystack = Str::lower($haystack);

        return collect($needles)
            ->contains(fn (string $needle): bool => $needle !== '' && str_contains($normalizedHaystack, Str::lower($needle)));
    }
}
