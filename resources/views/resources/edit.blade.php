@extends('layouts.app')
@section('title',trans('messages.actions.edit'))
@section('content')
<div class="container content">
    @include('marketplace::_breadcrumbs', ['items' => [['label' => $resource->name, 'url' => route('marketplace.resources.show', $resource)], ['label' => trans('messages.actions.edit')]]])
    <div class="d-flex align-items-center gap-3 mb-4">
        <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary text-white fs-3" style="width: 3.25rem; height: 3.25rem;"><i class="bi bi-pencil" aria-hidden="true"></i></span>
        <div><div class="small text-muted">@lang('messages.actions.edit')</div><h1 class="h2 mb-0">{{ $resource->name }}</h1></div>
    </div>
    <form method="POST" action="{{ route('marketplace.resources.update',$resource) }}" enctype="multipart/form-data" id="marketplace-resource-form">@method('PUT') @include('marketplace::resources._form')</form>
</div>
@endsection
