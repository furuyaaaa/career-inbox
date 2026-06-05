<?php

namespace App\Http\Controllers;

use App\Models\PreferenceProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PreferenceProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('preferences.edit', [
            'profile' => PreferenceProfile::primary($request->user()->id),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $profile = PreferenceProfile::primary($request->user()->id);
        $profile->update($this->validated($request));

        return redirect()
            ->route('preferences.edit')
            ->with('status', '希望条件を更新しました。');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'desired_salary_min' => ['nullable', 'integer', 'min:0', 'max:3000'],
            'preferred_occupations_text' => ['nullable', 'string', 'max:1000'],
            'preferred_industries_text' => ['nullable', 'string', 'max:1000'],
            'preferred_locations_text' => ['nullable', 'string', 'max:1000'],
            'remote_required' => ['nullable', 'boolean'],
            'preferred_remote_types_text' => ['nullable', 'string', 'max:1000'],
            'preferred_technologies_text' => ['nullable', 'string', 'max:1000'],
            'excluded_keywords_text' => ['nullable', 'string', 'max:1000'],
        ]);

        return [
            'desired_salary_min' => $data['desired_salary_min'] ?? null,
            'preferred_occupations' => $this->listFromText($data['preferred_occupations_text'] ?? ''),
            'preferred_industries' => $this->listFromText($data['preferred_industries_text'] ?? ''),
            'preferred_locations' => $this->listFromText($data['preferred_locations_text'] ?? ''),
            'remote_required' => (bool) ($data['remote_required'] ?? false),
            'preferred_remote_types' => $this->listFromText($data['preferred_remote_types_text'] ?? ''),
            'preferred_technologies' => $this->listFromText($data['preferred_technologies_text'] ?? ''),
            'excluded_keywords' => $this->listFromText($data['excluded_keywords_text'] ?? ''),
        ];
    }

    private function listFromText(string $text): array
    {
        return collect(explode(',', $text))
            ->map(fn (string $value): string => trim($value))
            ->filter()
            ->values()
            ->all();
    }
}
