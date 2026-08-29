@extends('admin.layouts.admin')

@section('title', trans('marketplace::admin.pending_resources.title'))

@section('content')
@include('marketplace::_breadcrumbs', ['admin' => true, 'items' => [['label' => trans('marketplace::admin.pending_resources.title')]]])
<div class="d-flex align-items-center gap-3 mb-4">
    <span class="d-flex align-items-center justify-content-center rounded bg-warning bg-opacity-10 text-warning fs-3" style="width: 3rem; height: 3rem;">
        <i class="bi bi-hourglass-split" aria-hidden="true"></i>
    </span>
    <div>
        <h1 class="h3 mb-1">@lang('marketplace::admin.pending_resources.title')</h1>
        <p class="text-muted mb-0">@lang('marketplace::admin.pending_resources.description')</p>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <strong>@lang('marketplace::admin.pending_resources.list')</strong>
        <span class="badge bg-warning text-dark rounded-pill">{{ $resources->total() }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>@lang('marketplace::admin.resource_list.name')</th>
                    <th>@lang('marketplace::admin.resource_list.version')</th>
                    <th>@lang('marketplace::admin.resource_list.author')</th>
                    <th>@lang('marketplace::admin.resource_list.status')</th>
                    <th>@lang('marketplace::admin.pending_resources.publication_date')</th>
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
                        <td><span class="badge bg-warning text-dark">@lang('marketplace::messages.status_labels.pending')</span></td>
                        <td class="text-nowrap">{{ format_date($resource->created_at, true) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <i class="bi bi-check-circle fs-1 text-success d-block mb-2" aria-hidden="true"></i>
                            <strong class="d-block">@lang('marketplace::admin.pending_resources.empty')</strong>
                            <span class="text-muted">@lang('marketplace::admin.pending_resources.empty_help')</span>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($resources->hasPages())<div class="mt-4">{{ $resources->links() }}</div>@endif
@endsection
