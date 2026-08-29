@extends('admin.layouts.admin')
@section('title', trans('marketplace::admin.tags.create'))
@section('content')
@include('marketplace::_breadcrumbs', ['admin' => true, 'items' => [['label' => trans('marketplace::admin.tags.title'), 'url' => route('marketplace.admin.tags.index')], ['label' => trans('marketplace::admin.tags.create')]]])
<div class="d-flex align-items-center justify-content-between gap-3 mb-4"><div class="d-flex align-items-center gap-3"><span class="d-flex align-items-center justify-content-center rounded bg-primary bg-opacity-10 text-primary fs-3" style="width: 3rem; height: 3rem;"><i class="bi bi-tag" aria-hidden="true"></i></span><div><h1 class="h3 mb-1">@lang('marketplace::admin.tags.create')</h1><p class="text-muted mb-0">@lang('marketplace::admin.tags.create_help')</p></div></div><a href="{{ route('marketplace.admin.tags.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1" aria-hidden="true"></i>@lang('messages.actions.back')</a></div>
<form method="POST" action="{{ route('marketplace.admin.tags.store') }}">@include('marketplace::admin.tags._form')</form>
@endsection
