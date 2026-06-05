<?php

namespace App\Http\Controllers;

use App\Models\JobPost;
use App\Models\PreferenceProfile;
use App\Services\JobMatchScorer;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class JobPostController extends Controller
{
    public function __construct(private readonly JobMatchScorer $scorer)
    {
    }

    public function index(Request $request): View
    {
        $query = JobPost::query()->where('user_id', $request->user()->id);
        $profile = PreferenceProfile::primary($request->user()->id);

        if ($request->filled('keyword')) {
            $keyword = $request->string('keyword')->toString();
            $query->where(function ($query) use ($keyword): void {
                $query
                    ->where('company_name', 'like', "%{$keyword}%")
                    ->orWhere('title', 'like', "%{$keyword}%")
                    ->orWhere('occupation', 'like', "%{$keyword}%")
                    ->orWhere('industry', 'like', "%{$keyword}%")
                    ->orWhere('location', 'like', "%{$keyword}%")
                    ->orWhere('memo', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('remote_type')) {
            $query->where('remote_type', $request->string('remote_type')->toString());
        }

        $scoredJobs = $query
            ->latest('received_at')
            ->latest()
            ->get()
            ->map(function (JobPost $jobPost) use ($profile): JobPost {
                $jobPost->match = $this->scorer->score($jobPost, $profile);

                return $jobPost;
            });

        if ($request->string('sort')->toString() === 'match') {
            $scoredJobs = $scoredJobs->sortByDesc(fn (JobPost $jobPost): int => $jobPost->match['score'])->values();
        }

        $jobPosts = $this->paginate($scoredJobs, $request);

        return view('jobs.index', [
            'jobPosts' => $jobPosts,
            'statuses' => $this->statuses(),
            'remoteTypes' => $this->remoteTypes(),
            'profile' => $profile,
        ]);
    }

    public function create(): View
    {
        return view('jobs.create', [
            'jobPost' => new JobPost([
                'status' => '未確認',
                'interest_level' => 3,
            ]),
            'statuses' => $this->statuses(),
            'remoteTypes' => $this->remoteTypes(),
            'employmentTypes' => $this->employmentTypes(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        JobPost::create([
            ...$this->validated($request),
            'user_id' => $request->user()->id,
        ]);

        return redirect()
            ->route('jobs.index')
            ->with('status', '求人を登録しました。');
    }

    public function show(Request $request, JobPost $job): View
    {
        $this->authorizeOwner($request, $job);

        return view('jobs.show', ['jobPost' => $job]);
    }

    public function edit(Request $request, JobPost $job): View
    {
        $this->authorizeOwner($request, $job);

        return view('jobs.edit', [
            'jobPost' => $job,
            'statuses' => $this->statuses(),
            'remoteTypes' => $this->remoteTypes(),
            'employmentTypes' => $this->employmentTypes(),
        ]);
    }

    public function update(Request $request, JobPost $job): RedirectResponse
    {
        $this->authorizeOwner($request, $job);

        $job->update($this->validated($request));

        return redirect()
            ->route('jobs.show', $job)
            ->with('status', '求人を更新しました。');
    }

    public function destroy(Request $request, JobPost $job): RedirectResponse
    {
        $this->authorizeOwner($request, $job);

        $job->delete();

        return redirect()
            ->route('jobs.index')
            ->with('status', '求人を削除しました。');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'industry' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'string', 'max:255'],
            'agent_name' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'salary_min' => ['nullable', 'integer', 'min:0', 'max:3000'],
            'salary_max' => ['nullable', 'integer', 'min:0', 'max:3000', 'gte:salary_min'],
            'employment_type' => ['nullable', 'string', 'max:255'],
            'remote_type' => ['nullable', 'string', 'max:255'],
            'technologies_text' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'string', 'max:255'],
            'interest_level' => ['required', 'integer', 'min:1', 'max:5'],
            'url' => ['nullable', 'url', 'max:2048'],
            'received_at' => ['nullable', 'date'],
            'memo' => ['nullable', 'string'],
        ]);

        $data['technologies'] = collect(explode(',', $data['technologies_text'] ?? ''))
            ->map(fn (string $technology): string => trim($technology))
            ->filter()
            ->values()
            ->all();

        unset($data['technologies_text']);

        return $data;
    }

    private function statuses(): array
    {
        return ['未確認', '気になる', '応募したい', '応募済み', '面談中', '見送り', '内定', '辞退'];
    }

    private function remoteTypes(): array
    {
        return ['フルリモート', 'ハイブリッド', '週3リモート', '出社中心', '不明'];
    }

    private function employmentTypes(): array
    {
        return ['正社員', '契約社員', '業務委託', '副業', '不明'];
    }

    private function authorizeOwner(Request $request, JobPost $jobPost): void
    {
        abort_unless($jobPost->user_id === $request->user()->id, 404);
    }

    /**
     * @param Collection<int, JobPost> $jobPosts
     */
    private function paginate(Collection $jobPosts, Request $request): LengthAwarePaginator
    {
        $perPage = 10;
        $page = LengthAwarePaginator::resolveCurrentPage();

        return new LengthAwarePaginator(
            $jobPosts->forPage($page, $perPage)->values(),
            $jobPosts->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ],
        );
    }
}
