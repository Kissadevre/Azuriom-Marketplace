@extends('layouts.app')
@section('title',trans('marketplace::messages.submit'))
@section('content')
<div class="container content">
    @include('marketplace::_breadcrumbs', ['items' => [['label' => trans('marketplace::messages.submit')]]])
    <div class="d-flex align-items-center gap-3 mb-4">
        <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary text-white fs-3" style="width: 3.25rem; height: 3.25rem;"><i class="bi bi-plus-lg" aria-hidden="true"></i></span>
        <h1 class="h2 mb-0">@lang('marketplace::messages.submit')</h1>
    </div>
    <form method="POST" action="{{ route('marketplace.resources.store') }}" enctype="multipart/form-data" id="marketplace-resource-form">@include('marketplace::resources._form')</form>
</div>
@endsection
