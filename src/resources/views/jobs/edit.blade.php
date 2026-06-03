@extends('layouts.app')

@section('title', '求人編集 | Career Inbox')

@section('content')
  <header class="page-head">
    <div>
      <p class="eyebrow">Edit</p>
      <h1>求人を編集</h1>
    </div>
    <a class="button secondary" href="{{ route('jobs.show', $jobPost) }}">詳細へ戻る</a>
  </header>

  <form class="panel form-grid" method="post" action="{{ route('jobs.update', $jobPost) }}">
    @method('PUT')
    @include('jobs._form')
    <div class="actions span-2">
      <button class="button" type="submit">更新する</button>
      <a class="button secondary" href="{{ route('jobs.show', $jobPost) }}">キャンセル</a>
    </div>
  </form>
@endsection
