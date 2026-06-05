@extends('layouts.app')

@section('title', '希望条件 | Career Inbox')

@section('content')
  <header class="page-head">
    <div>
      <p class="eyebrow">Preferences</p>
      <h1>希望条件</h1>
      <p class="muted">マッチングスコアの基準になる条件です。候補ボタンで選択しつつ、候補にない条件は追加欄から追記できます。</p>
    </div>
    <a class="button secondary" href="{{ route('jobs.index', ['sort' => 'match']) }}">マッチ順を見る</a>
  </header>

  <form class="panel form-grid" method="post" action="{{ route('preferences.update') }}">
    @csrf
    @method('PUT')

    @if ($errors->any())
      <div class="error-box span-2">入力内容を確認してください。</div>
    @endif

    <label>
      希望年収下限（万円）
      <input name="desired_salary_min" type="number" min="0" max="3000" value="{{ old('desired_salary_min', $profile->desired_salary_min) }}">
      @error('desired_salary_min')<span class="muted">{{ $message }}</span>@enderror
    </label>

    <label>
      リモートを優先
      <select name="remote_required">
        <option value="1" @selected(old('remote_required', $profile->remote_required) == true)>はい</option>
        <option value="0" @selected(old('remote_required', $profile->remote_required) == false)>いいえ</option>
      </select>
    </label>

    @php
      $optionGroups = [
          'preferred_occupations_text' => [
              'label' => '希望職種カテゴリ',
              'value' => old('preferred_occupations_text', implode(', ', $profile->preferred_occupations ?? [])),
              'placeholder' => '営業, マーケティング, 経理, カスタマーサクセス',
              'options' => ['営業', 'マーケティング', 'カスタマーサクセス', '企画', '管理部門', '経理', '人事', 'エンジニア', 'デザイン', '販売', 'コンサルタント'],
          ],
          'preferred_industries_text' => [
              'label' => '希望業界',
              'value' => old('preferred_industries_text', implode(', ', $profile->preferred_industries ?? [])),
              'placeholder' => 'IT, 人材, メーカー, 医療, 教育',
              'options' => ['IT', 'SaaS', '人材', '教育', '金融', '医療', 'メーカー', '小売', '広告', '不動産', 'コンサルティング'],
          ],
          'preferred_locations_text' => [
              'label' => '希望勤務地',
              'value' => old('preferred_locations_text', implode(', ', $profile->preferred_locations ?? [])),
              'placeholder' => '東京, 全国',
              'options' => ['東京', '神奈川', '埼玉', '千葉', '大阪', '京都', '兵庫', '名古屋', '福岡', '札幌', '全国', '海外'],
          ],
          'preferred_remote_types_text' => [
              'label' => '希望リモート条件',
              'value' => old('preferred_remote_types_text', implode(', ', $profile->preferred_remote_types ?? [])),
              'placeholder' => 'フルリモート, ハイブリッド, 週3リモート',
              'options' => ['フルリモート', 'ハイブリッド', '週3リモート', '週1リモート', '出社中心', '不明'],
          ],
          'preferred_technologies_text' => [
              'label' => '活かしたいスキル・経験キーワード',
              'value' => old('preferred_technologies_text', implode(', ', $profile->preferred_technologies ?? [])),
              'placeholder' => '法人営業, CRM, データ分析, 経理, 英語, Laravel',
              'options' => ['法人営業', 'CRM', 'データ分析', '企画', '広告運用', '採用', '経理', '月次決算', '顧客折衝', '英語', 'Laravel', 'Python', 'AWS'],
          ],
          'excluded_keywords_text' => [
              'label' => '除外キーワード',
              'value' => old('excluded_keywords_text', implode(', ', $profile->excluded_keywords ?? [])),
              'placeholder' => 'SES, 常駐のみ',
              'options' => ['SES', '常駐のみ', '飛び込み営業', '夜勤', '転勤あり', '土日勤務', '低単価', '完全出社'],
          ],
      ];
    @endphp

    @foreach ($optionGroups as $name => $group)
      <div class="option-group span-2" data-option-group>
        <label>
          {{ $group['label'] }}
          <input data-option-input name="{{ $name }}" value="{{ $group['value'] }}" placeholder="{{ $group['placeholder'] }}">
        </label>
        <div class="option-buttons" aria-label="{{ $group['label'] }}の候補">
          @foreach ($group['options'] as $option)
            <button class="option-button" type="button" data-option-value="{{ $option }}">{{ $option }}</button>
          @endforeach
        </div>
        <div class="custom-option-row">
          <label>
            候補にない条件を追加
            <input data-custom-option-input placeholder="{{ $group['label'] }}を追加">
          </label>
          <button class="button secondary" type="button" data-custom-option-add>追加</button>
        </div>
      </div>
    @endforeach

    <div class="actions span-2">
      <button class="button" type="submit">保存する</button>
      <a class="button secondary" href="{{ route('jobs.index') }}">キャンセル</a>
    </div>
  </form>

  <script>
    const splitValues = (value) => value
      .split(',')
      .map((item) => item.trim())
      .filter(Boolean);

    const syncButtons = (group) => {
      const input = group.querySelector('[data-option-input]');
      const values = splitValues(input.value);

      group.querySelectorAll('[data-option-value]').forEach((button) => {
        button.classList.toggle('active', values.includes(button.dataset.optionValue));
      });
    };

    document.querySelectorAll('[data-option-group]').forEach((group) => {
      const input = group.querySelector('[data-option-input]');

      group.querySelectorAll('[data-option-value]').forEach((button) => {
        button.addEventListener('click', () => {
          const value = button.dataset.optionValue;
          const values = splitValues(input.value);
          const nextValues = values.includes(value)
            ? values.filter((item) => item !== value)
            : [...values, value];

          input.value = nextValues.join(', ');
          syncButtons(group);
        });
      });

      const customInput = group.querySelector('[data-custom-option-input]');
      const customAddButton = group.querySelector('[data-custom-option-add]');
      const addCustomValue = () => {
        const value = customInput.value.trim();

        if (!value) {
          return;
        }

        const values = splitValues(input.value);

        if (!values.includes(value)) {
          input.value = [...values, value].join(', ');
        }

        customInput.value = '';
        syncButtons(group);
        input.focus();
      };

      customAddButton.addEventListener('click', addCustomValue);
      customInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
          event.preventDefault();
          addCustomValue();
        }
      });

      input.addEventListener('input', () => syncButtons(group));
      syncButtons(group);
    });
  </script>
@endsection
