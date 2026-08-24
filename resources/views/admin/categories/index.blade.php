@extends('admin.layouts.admin')

@section('title', trans('marketplace::admin.categories.title'))

@section('content')
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
    <div class="d-flex align-items-center gap-3">
        <span class="d-flex align-items-center justify-content-center rounded bg-primary bg-opacity-10 text-primary fs-3" style="width: 3rem; height: 3rem;">
            <i class="bi bi-grid" aria-hidden="true"></i>
        </span>
        <div>
            <h1 class="h3 mb-1">@lang('marketplace::admin.categories.title')</h1>
            <p class="text-muted mb-0">@lang('marketplace::admin.categories.description', ['count' => $categories->count()])</p>
        </div>
    </div>
    <a href="{{ route('marketplace.admin.categories.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>@lang('messages.actions.add')
    </a>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <strong>@lang('marketplace::admin.categories.list')</strong>
        <span class="badge bg-primary rounded-pill">{{ $categories->count() }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>@lang('messages.fields.name')</th>
                    <th>@lang('marketplace::admin.categories.access')</th>
                    <th>@lang('marketplace::admin.categories.status')</th>
                    <th class="text-center">@lang('marketplace::admin.categories.resources')</th>
                    <th class="text-center">@lang('marketplace::admin.categories.position')</th>
                    <th class="text-end"><span class="visually-hidden">@lang('marketplace::admin.categories.actions')</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <span class="d-flex align-items-center justify-content-center rounded bg-body-secondary fs-4" style="width: 2.75rem; height: 2.75rem;">
                                    <i class="{{ $category->icon }}" aria-hidden="true"></i>
                                </span>
                                <div style="max-width: 24rem;">
                                    <strong class="d-block">{{ $category->name }}</strong>
                                    <small class="text-muted d-block text-truncate">{{ $category->description ?: '/'.$category->slug }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($category->roles === null)
                                <span class="badge bg-success-subtle text-success-emphasis"><i class="bi bi-globe2 me-1" aria-hidden="true"></i>@lang('marketplace::admin.categories.public')</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning-emphasis"><i class="bi bi-lock me-1" aria-hidden="true"></i>@lang('marketplace::admin.categories.restricted')</span>
                                <small class="text-muted d-block mt-1">@lang('marketplace::admin.categories.roles', ['count' => count($category->roles)])</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $category->is_enabled ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis' }}">
                                <i class="bi bi-{{ $category->is_enabled ? 'check-circle' : 'pause-circle' }} me-1" aria-hidden="true"></i>
                                @lang($category->is_enabled ? 'marketplace::admin.categories.active' : 'marketplace::admin.categories.disabled')
                            </span>
                        </td>
                        <td class="text-center"><span class="badge bg-primary rounded-pill">{{ $category->resources_count }}</span></td>
                        <td class="text-center text-muted">{{ $category->position }}</td>
                        <td class="text-end text-nowrap">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('marketplace.admin.categories.edit', $category) }}" title="@lang('messages.actions.edit')" data-bs-toggle="tooltip">
                                <i class="bi bi-pencil" aria-hidden="true"></i><span class="visually-hidden">@lang('messages.actions.edit')</span>
                            </a>
                            @if($category->resources_count === 0)
                                <a class="btn btn-sm btn-outline-danger" href="{{ route('marketplace.admin.categories.destroy', $category) }}" title="@lang('messages.actions.delete')" data-bs-toggle="tooltip" data-confirm="delete">
                                    <i class="bi bi-trash" aria-hidden="true"></i><span class="visually-hidden">@lang('messages.actions.delete')</span>
                                </a>
                            @else
                                <span class="d-inline-block" title="@lang('marketplace::admin.categories.not_empty')" data-bs-toggle="tooltip">
                                    <button class="btn btn-sm btn-outline-secondary" type="button" disabled>
                                        <i class="bi bi-trash" aria-hidden="true"></i><span class="visually-hidden">@lang('messages.actions.delete')</span>
                                    </button>
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="bi bi-grid fs-1 text-muted d-block mb-2" aria-hidden="true"></i>
                            <strong class="d-block">@lang('marketplace::admin.categories.empty')</strong>
                            <span class="text-muted">@lang('marketplace::admin.categories.empty_help')</span>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
