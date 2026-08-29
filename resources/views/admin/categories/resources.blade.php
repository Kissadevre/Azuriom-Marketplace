@extends('admin.layouts.admin')

@section('title', trans('marketplace::admin.resource_list.title', ['category' => $category->name]))

@section('content')
@include('marketplace::_breadcrumbs', ['admin' => true, 'items' => [['label' => trans('marketplace::admin.categories.title'), 'url' => route('marketplace.admin.categories.index')], ['label' => $category->name]]])
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
    <div class="d-flex align-items-center gap-3">
        <span class="d-flex align-items-center justify-content-center rounded bg-primary bg-opacity-10 text-primary fs-3" style="width: 3rem; height: 3rem;">
            <i class="{{ $category->icon }}" aria-hidden="true"></i>
        </span>
        <div>
            <h1 class="h3 mb-1">@lang('marketplace::admin.resource_list.title', ['category' => $category->name])</h1>
            <p class="text-muted mb-0">@lang('marketplace::admin.resource_list.description')</p>
        </div>
    </div>
    <a href="{{ route('marketplace.admin.categories.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>@lang('messages.actions.back')
    </a>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <strong>@lang('marketplace::admin.resource_list.list')</strong>
        <span class="badge bg-primary rounded-pill">{{ $resources->total() }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>@lang('marketplace::admin.resource_list.name')</th>
                    <th>@lang('marketplace::admin.resource_list.version')</th>
                    <th>@lang('marketplace::admin.resource_list.author')</th>
                    <th>@lang('marketplace::admin.resource_list.status')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($resources as $resource)
                    <tr>
                        <td>
                            <a href="{{ route('marketplace.resources.show', $resource) }}" class="fw-semibold text-decoration-none">
                                {{ $resource->name }}<i class="bi bi-box-arrow-up-right ms-1 small" aria-hidden="true"></i>
                            </a>
                        </td>
                        <td>{{ $resource->version ?: '—' }}</td>
                        <td>{{ $resource->author->name }}</td>
                        <td>
                            <span class="badge bg-{{ $resource->status === 'published' ? 'success' : ($resource->status === 'pending' ? 'warning text-dark' : 'danger') }}">
                                @lang('marketplace::messages.status_labels.'.$resource->status)
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-5">
                            <i class="bi bi-box-seam fs-1 text-muted d-block mb-2" aria-hidden="true"></i>
                            <strong class="d-block">@lang('marketplace::admin.resource_list.empty')</strong>
                            <span class="text-muted">@lang('marketplace::admin.resource_list.empty_help')</span>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($resources->hasPages())
    <div class="mt-4">{{ $resources->links() }}</div>
@endif
@endsection
