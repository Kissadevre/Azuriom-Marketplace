@extends('admin.layouts.admin')
@section('title',trans('marketplace::admin.categories.title'))
@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card h-100"><div class="card-body d-flex align-items-center justify-content-between gap-3">
            <div><div class="text-muted mb-1">@lang('marketplace::admin.stats.published')</div><div class="fs-2 fw-semibold">{{ $publishedResources }}</div></div>
            <i class="bi bi-check-circle-fill fs-1 text-success" aria-hidden="true"></i>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card h-100"><div class="card-body d-flex align-items-center justify-content-between gap-3">
            <div><div class="text-muted mb-1">@lang('marketplace::admin.stats.pending')</div><div class="fs-2 fw-semibold">{{ $pendingResources }}</div></div>
            <i class="bi bi-hourglass-split fs-1 text-warning" aria-hidden="true"></i>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card h-100"><div class="card-body d-flex align-items-center justify-content-between gap-3">
            <div><div class="text-muted mb-1">@lang('marketplace::admin.stats.spent')</div><div class="fs-2 fw-semibold">{{ format_money($spentPoints) }}</div></div>
            <i class="bi bi-coin fs-1 text-primary" aria-hidden="true"></i>
        </div></div>
    </div>
</div>

@include('marketplace::admin._nav')

<div class="d-flex justify-content-between mb-3">
    <h1>@lang('marketplace::admin.categories.title')</h1>
    <a href="{{ route('marketplace.admin.categories.create') }}" class="btn btn-primary">@lang('messages.actions.add')</a>
</div>

<div class="card"><div class="table-responsive"><table class="table mb-0"><thead><tr><th>@lang('messages.fields.name')</th><th>@lang('marketplace::admin.categories.access')</th><th>@lang('marketplace::admin.categories.resources')</th><th></th></tr></thead><tbody>@foreach($categories as $category)<tr><td><i class="{{ $category->icon }}"></i> {{ $category->name }}</td><td>{{ $category->roles===null?trans('marketplace::admin.categories.public'):trans('marketplace::admin.categories.restricted') }}</td><td>{{ $category->resources_count }}</td><td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('marketplace.admin.categories.edit',$category) }}">@lang('messages.actions.edit')</a><form class="d-inline" method="POST" action="{{ route('marketplace.admin.categories.destroy',$category) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">@lang('messages.actions.delete')</button></form></td></tr>@endforeach</tbody></table></div></div>
@endsection
