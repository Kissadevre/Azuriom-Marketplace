@extends('admin.layouts.admin')

@section('title', trans('marketplace::admin.archived_resources.title'))

@section('content')
<div class="d-flex align-items-center gap-3 mb-4">
    <span class="d-flex align-items-center justify-content-center rounded bg-secondary bg-opacity-10 text-secondary fs-3" style="width: 3rem; height: 3rem;">
        <i class="bi bi-archive" aria-hidden="true"></i>
    </span>
    <div>
        <h1 class="h3 mb-1">@lang('marketplace::admin.archived_resources.title')</h1>
        <p class="text-muted mb-0">@lang('marketplace::admin.archived_resources.description')</p>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <strong>@lang('marketplace::admin.archived_resources.list')</strong>
        <span class="badge bg-secondary rounded-pill">{{ $resources->total() }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>@lang('marketplace::admin.resource_list.name')</th><th>@lang('marketplace::admin.resource_list.version')</th><th>@lang('marketplace::admin.resource_list.author')</th><th>@lang('marketplace::admin.resource_list.status')</th><th>@lang('marketplace::admin.archived_resources.archived_at')</th><th></th></tr></thead>
            <tbody>
                @forelse($resources as $resource)
                    @php
                        $statusClass = match ($resource->status) {
                            'published' => 'bg-success',
                            'pending' => 'bg-warning text-dark',
                            'rejected' => 'bg-danger',
                            default => 'bg-secondary',
                        };
                    @endphp
                    <tr>
                        <td><strong>{{ $resource->name }}</strong><small class="d-block text-muted">{{ $resource->category->name }}</small></td>
                        <td>{{ $resource->version ?: '—' }}</td>
                        <td><a href="{{ route('admin.users.edit', $resource->author) }}">{{ $resource->author->name }}</a></td>
                        <td><span class="badge {{ $statusClass }}">{{ trans('marketplace::messages.status_labels.'.$resource->status) }}</span></td>
                        <td class="text-nowrap">{{ format_date($resource->archived_at, true) }}</td>
                        <td class="text-end"><button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#restoreResourceModal" data-restore-action="{{ route('marketplace.admin.resources.archived.restore', $resource->uuid) }}" data-resource-name="{{ $resource->name }}"><i class="bi bi-arrow-counterclockwise me-1" aria-hidden="true"></i>@lang('marketplace::admin.archived_resources.restore')</button></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-5"><i class="bi bi-archive fs-1 text-muted d-block mb-2" aria-hidden="true"></i><strong class="d-block">@lang('marketplace::admin.archived_resources.empty')</strong><span class="text-muted">@lang('marketplace::admin.archived_resources.empty_help')</span></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($resources->hasPages())<div class="mt-4">{{ $resources->links() }}</div>@endif

<div class="modal fade" id="restoreResourceModal" tabindex="-1" aria-labelledby="restoreResourceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <div class="modal-header"><h2 class="modal-title h5" id="restoreResourceModalLabel">@lang('marketplace::admin.archived_resources.restore')</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="@lang('messages.actions.close')"></button></div>
        <div class="modal-body" data-restore-message></div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('messages.actions.cancel')</button><form method="POST" data-restore-form>@csrf @method('PATCH')<button class="btn btn-success"><i class="bi bi-arrow-counterclockwise me-1" aria-hidden="true"></i>@lang('marketplace::admin.archived_resources.restore')</button></form></div>
    </div></div>
</div>
@endsection

@push('footer-scripts')
<script>
    document.getElementById('restoreResourceModal')?.addEventListener('show.bs.modal', (event) => {
        const button = event.relatedTarget;
        const modal = event.currentTarget;
        const message = @json(trans('marketplace::admin.archived_resources.restore_confirm', ['resource' => ':resource']));
        modal.querySelector('[data-restore-message]').textContent = message.replace(':resource', button.dataset.resourceName);
        modal.querySelector('[data-restore-form]').action = button.dataset.restoreAction;
    });
</script>
@endpush
