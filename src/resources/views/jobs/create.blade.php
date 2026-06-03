@extends('layouts.app')

@section('title', '求人登録 | Career Inbox')

@section('content')
  <header class="page-head">
    <div>
      <p class="eyebrow">Create</p>
      <h1>求人を登録</h1>
    </div>
    <a class="button secondary" href="{{ route('jobs.index') }}">一覧へ戻る</a>
  </header>

  <form class="panel form-grid" method="post" action="{{ route('jobs.store') }}">
    @include('jobs._form')
    <div class="actions span-2">
      <button class="button" type="submit">登録する</button>
      <a class="button secondary" href="{{ route('jobs.index') }}">キャンセル</a>
    </div>
  </form>
@endsection
