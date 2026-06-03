@csrf

@if ($errors->any())
  <div class="error-box span-2">
    入力内容を確認してください。
  </div>
@endif

<label>
  会社名
  <input name="company_name" value="{{ old('company_name', $jobPost->company_name) }}" required>
  @error('company_name')<span class="muted">{{ $message }}</span>@enderror
</label>

<label>
  求人タイトル
  <input name="title" value="{{ old('title', $jobPost->title) }}" required>
  @error('title')<span class="muted">{{ $message }}</span>@enderror
</label>

<label>
  職種カテゴリ
  <input name="occupation" value="{{ old('occupation', $jobPost->occupation) }}" placeholder="営業 / マーケティング / 経理 / エンジニア">
</label>

<label>
  業界
  <input name="industry" value="{{ old('industry', $jobPost->industry) }}" placeholder="IT / 人材 / メーカー / 医療">
</label>

<label>
  掲載元
  <input name="source" value="{{ old('source', $jobPost->source) }}" placeholder="Green / Gmail / エージェント">
</label>

<label>
  エージェント名
  <input name="agent_name" value="{{ old('agent_name', $jobPost->agent_name) }}">
</label>

<label>
  勤務地
  <input name="location" value="{{ old('location', $jobPost->location) }}" placeholder="東京 / 全国">
</label>

<label>
  雇用形態
  <select name="employment_type">
    <option value="">未選択</option>
    @foreach ($employmentTypes as $employmentType)
      <option value="{{ $employmentType }}" @selected(old('employment_type', $jobPost->employment_type) === $employmentType)>{{ $employmentType }}</option>
    @endforeach
  </select>
</label>

<label>
  年収下限（万円）
  <input name="salary_min" type="number" min="0" max="3000" value="{{ old('salary_min', $jobPost->salary_min) }}">
</label>

<label>
  年収上限（万円）
  <input name="salary_max" type="number" min="0" max="3000" value="{{ old('salary_max', $jobPost->salary_max) }}">
  @error('salary_max')<span class="muted">{{ $message }}</span>@enderror
</label>

<label>
  リモート条件
  <select name="remote_type">
    <option value="">未選択</option>
    @foreach ($remoteTypes as $remoteType)
      <option value="{{ $remoteType }}" @selected(old('remote_type', $jobPost->remote_type) === $remoteType)>{{ $remoteType }}</option>
    @endforeach
  </select>
</label>

<label>
  ステータス
  <select name="status" required>
    @foreach ($statuses as $status)
      <option value="{{ $status }}" @selected(old('status', $jobPost->status) === $status)>{{ $status }}</option>
    @endforeach
  </select>
</label>

<label>
  興味度
  <select name="interest_level" required>
    @for ($level = 1; $level <= 5; $level++)
      <option value="{{ $level }}" @selected((int) old('interest_level', $jobPost->interest_level) === $level)>{{ $level }}</option>
    @endfor
  </select>
</label>

<label>
  受信日
  <input name="received_at" type="date" value="{{ old('received_at', optional($jobPost->received_at)->format('Y-m-d')) }}">
</label>

<label class="span-2">
  スキル・経験キーワード
  <input name="technologies_text" value="{{ old('technologies_text', implode(', ', $jobPost->technologies ?? [])) }}" placeholder="法人営業, CRM, データ分析, 経理, 英語, Laravel">
</label>

<label class="span-2">
  求人URL
  <input name="url" type="url" value="{{ old('url', $jobPost->url) }}" placeholder="https://example.com/jobs/123">
  @error('url')<span class="muted">{{ $message }}</span>@enderror
</label>

<label class="span-2">
  メモ
  <textarea name="memo">{{ old('memo', $jobPost->memo) }}</textarea>
</label>
