<?php

namespace Tests\Feature;

use App\Services\JobMatchScorer;
use App\Services\MatchingService;
use Tests\TestCase;

class MatchingServiceBindingTest extends TestCase
{
    public function test_matching_service_resolves_to_current_php_scorer(): void
    {
        $service = $this->app->make(MatchingService::class);

        $this->assertInstanceOf(JobMatchScorer::class, $service);
    }
}
