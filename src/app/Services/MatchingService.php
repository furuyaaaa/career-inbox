<?php

namespace App\Services;

use App\Models\JobPost;
use App\Models\PreferenceProfile;

interface MatchingService
{
    /**
     * @return array{score: int, reasons: array<int, string>}
     */
    public function score(JobPost $jobPost, PreferenceProfile $profile): array;
}
