@extends('admin.layouts.admin')

@section('title', $category->name)

@section('content')
@include('marketplace::_breadcrumbs', ['admin' => true, 'items' => [['label' => trans('marketplace::admin.categories.title'), 'url' => route('marketplace.admin.categories.index')], ['label' => $category->name]]])
<div class="d-flex align-items-center justify-content-between gap-3 mb-4">
    <div class="d-flex align-items-center gap-3">
        <span class="d-flex align-items-center justify-content-center rounded bg-primary bg-opacity-10 text-primary fs-3" style="width: 3rem; height: 3rem;"><i class="{{ $category->icon }}" aria-hidden="true"></i></span>
        <div><h1 class="h3 mb-1">@lang('marketplace::admin.categories.edit', ['category' => $category->name])</h1><p class="text-muted mb-0">@lang('marketplace::admin.categories.edit_help')</p></div>
    </div>
    <a href="{{ route('marketplace.admin.categories.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1" aria-hidden="true"></i>@lang('messages.actions.back')</a>
</div>

<form method="POST" action="{{ route('marketplace.admin.categories.update', $category) }}">
    @method('PUT')
    @include('marketplace::admin.categories._form')
</form>
@endsection
