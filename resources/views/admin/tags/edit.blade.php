@extends('admin.layouts.admin')
@section('title', $tag->name)
@section('content')
<div class="d-flex align-items-center justify-content-between gap-3 mb-4"><div class="d-flex align-items-center gap-3"><span class="d-flex align-items-center justify-content-center rounded fs-3 text-white" style="width: 3rem; height: 3rem; background: {{ $tag->color }};"><i class="bi bi-tag" aria-hidden="true"></i></span><div><h1 class="h3 mb-1">@lang('marketplace::admin.tags.edit', ['tag' => $tag->name])</h1><p class="text-muted mb-0">@lang('marketplace::admin.tags.edit_help')</p></div></div><a href="{{ route('marketplace.admin.tags.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1" aria-hidden="true"></i>@lang('messages.actions.back')</a></div>
<form method="POST" action="{{ route('marketplace.admin.tags.update', $tag) }}">@method('PUT') @include('marketplace::admin.tags._form')</form>
@endsection
