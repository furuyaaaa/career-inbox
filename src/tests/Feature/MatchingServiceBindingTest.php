<?php

namespace Tests\Feature;

use App\Services\JobMatchScorer;
use App\Services\MatchingService;
use App\Services\PythonMatchingService;
use Tests\TestCase;

class MatchingServiceBindingTest extends TestCase
{
    public function test_matching_service_resolves_to_php_scorer_by_default(): void
    {
        config(['matching.driver' => 'php']);

        $service = $this->app->make(MatchingService::class);

        $this->assertInstanceOf(JobMatchScorer::class, $service);
    }

    public function test_matching_service_can_resolve_to_python_driver(): void
    {
        config(['matching.driver' => 'python']);

        $service = $this->app->make(MatchingService::class);

        $this->assertInstanceOf(PythonMatchingService::class, $service);
    }
}
