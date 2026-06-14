<?php

namespace App\Http\Controllers;

use App\Models\JobPost;
use App\Models\PreferenceProfile;
use App\Services\JobMatchScorer;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly JobMatchScorer $scorer)
    {
    }

    public function index(Request $request): View
    {
        $userId = $request->user()->id;
        $profile = PreferenceProfile::primary($userId);
        $jobPosts = JobPost::query()
            ->where('user_id', $userId)
            ->latest('received_at')
            ->latest()
            ->get();

        $scoredJobs = $jobPosts
            ->map(function (JobPost $jobPost) use ($profile): JobPost {
                $jobPost->match = $this->scorer->score($jobPost, $profile);

                return $jobPost;
            });

        return view('dashboard.index', [
            'totalJobs' => $jobPosts->count(),
            'statusCounts' => $this->statusCounts($jobPosts),
            'focusCount' => $jobPosts->whereIn('status', ['気になる', '応募したい'])->count(),
            'appliedCount' => $jobPosts->whereIn('status', ['応募済み', '面談中', '内定'])->count(),
            'topJobs' => $scoredJobs->sortByDesc(fn (JobPost $jobPost): int => $jobPost->match['score'])->take(5),
            'recentGmailJobs' => $jobPosts->where('source', 'Gmail')->take(5),
        ]);
    }

    /**
     * @param Collection<int, JobPost> $jobPosts
     * @return array<string, int>
     */
    private function statusCounts(Collection $jobPosts): array
    {
        $statuses = ['未確認', '気になる', '応募したい', '応募済み', '面談中', '見送り', '内定', '辞退'];

        return collect($statuses)
            ->mapWithKeys(fn (string $status): array => [$status => $jobPosts->where('status', $status)->count()])
            ->all();
    }
}
