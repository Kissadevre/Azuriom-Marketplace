@extends('admin.layouts.admin')

@section('title', trans('marketplace::admin.tags.title'))

@section('content')
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
    <div class="d-flex align-items-center gap-3">
        <span class="d-flex align-items-center justify-content-center rounded bg-primary bg-opacity-10 text-primary fs-3" style="width: 3rem; height: 3rem;"><i class="bi bi-tags" aria-hidden="true"></i></span>
        <div><h1 class="h3 mb-1">@lang('marketplace::admin.tags.title')</h1><p class="text-muted mb-0">@lang('marketplace::admin.tags.description', ['count' => $tags->count()])</p></div>
    </div>
    <a href="{{ route('marketplace.admin.tags.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i>@lang('messages.actions.add')</a>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between"><strong>@lang('marketplace::admin.tags.list')</strong><span class="badge bg-primary rounded-pill">{{ $tags->count() }}</span></div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>@lang('messages.fields.name')</th><th>@lang('marketplace::admin.tags.status')</th><th class="text-center">@lang('marketplace::admin.tags.resources')</th><th class="text-center">@lang('marketplace::admin.tags.position')</th><th class="text-end"><span class="visually-hidden">@lang('marketplace::admin.tags.actions')</span></th></tr></thead>
            <tbody>
                @forelse($tags as $tag)
                    <tr>
                        <td><div class="d-flex align-items-center gap-3"><span class="rounded-circle border" style="width: 1.25rem; height: 1.25rem; background: {{ $tag->color }};"></span><div><strong class="d-block">{{ $tag->name }}</strong><small class="text-muted">{{ $tag->description ?: '/'.$tag->slug }}</small></div></div></td>
                        <td><span class="badge {{ $tag->is_enabled ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis' }}">@lang($tag->is_enabled ? 'marketplace::admin.tags.active' : 'marketplace::admin.tags.disabled')</span></td>
                        <td class="text-center"><span class="badge bg-primary rounded-pill">{{ $tag->resources_count }}</span></td>
                        <td class="text-center text-muted">{{ $tag->position }}</td>
                        <td class="text-end text-nowrap">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('marketplace.admin.tags.edit', $tag) }}" title="@lang('messages.actions.edit')" data-bs-toggle="tooltip"><i class="bi bi-pencil" aria-hidden="true"></i><span class="visually-hidden">@lang('messages.actions.edit')</span></a>
                            @if($tag->resources_count === 0)
                                <a class="btn btn-sm btn-outline-danger" href="{{ route('marketplace.admin.tags.destroy', $tag) }}" title="@lang('messages.actions.delete')" data-bs-toggle="tooltip" data-confirm="delete"><i class="bi bi-trash" aria-hidden="true"></i><span class="visually-hidden">@lang('messages.actions.delete')</span></a>
                            @else
                                <span class="d-inline-block" title="@lang('marketplace::admin.tags.not_empty')" data-bs-toggle="tooltip"><button class="btn btn-sm btn-outline-secondary" type="button" disabled><i class="bi bi-trash" aria-hidden="true"></i><span class="visually-hidden">@lang('messages.actions.delete')</span></button></span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-5"><i class="bi bi-tags fs-1 text-muted d-block mb-2" aria-hidden="true"></i><strong class="d-block">@lang('marketplace::admin.tags.empty')</strong><span class="text-muted">@lang('marketplace::admin.tags.empty_help')</span></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
