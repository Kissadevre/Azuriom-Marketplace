@extends('admin.layouts.admin')

@section('title', trans('marketplace::admin.reports.title'))

@section('content')
@include('marketplace::_breadcrumbs', ['admin' => true, 'items' => [['label' => trans('marketplace::admin.reports.title')]]])
<div class="d-flex align-items-center gap-3 mb-4">
    <span class="d-flex align-items-center justify-content-center rounded bg-danger bg-opacity-10 text-danger fs-3" style="width: 3rem; height: 3rem;">
        <i class="bi bi-flag" aria-hidden="true"></i>
    </span>
    <div>
        <h1 class="h3 mb-1">@lang('marketplace::admin.reports.title')</h1>
        <p class="text-muted mb-0">@lang('marketplace::admin.reports.description')</p>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>@lang('marketplace::admin.reports.received')</strong>
        <span class="badge bg-danger rounded-pill">{{ $reports->total() }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>@lang('marketplace::admin.reports.content')</th><th>@lang('marketplace::admin.reports.reporter')</th><th>@lang('marketplace::admin.reports.reason')</th><th>@lang('marketplace::admin.reports.date')</th><th></th></tr></thead>
            <tbody>
                @forelse($reports as $report)
                    @php
                        $isResource = $report->reportable_type === 'marketplace.resources';
                        $comment = $isResource ? null : $report->reportable;
                        $targetResource = $isResource ? $report->reportable : $comment?->resource;
                    @endphp
                    <tr>
                        <td style="min-width: 16rem;">
                            <span class="badge {{ $isResource ? 'bg-primary' : 'bg-info text-dark' }} mb-1">@lang($isResource ? 'marketplace::admin.reports.resource' : 'marketplace::admin.reports.comment')</span>
                            <strong class="d-block">{{ $report->subject }}</strong>
                        </td>
                        <td>{{ $report->reporter->name }}</td>
                        <td style="min-width: 18rem; white-space: pre-wrap;">{{ $report->reason }}</td>
                        <td class="text-nowrap">{{ format_date($report->created_at, true) }}</td>
                        <td class="text-end">
                            @if($isResource && $targetResource)
                                <a href="{{ route('marketplace.resources.show', $targetResource) }}" class="btn btn-sm btn-outline-primary" title="@lang('marketplace::admin.reports.view')" data-bs-toggle="tooltip">
                                    <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i><span class="visually-hidden">@lang('marketplace::admin.reports.view')</span>
                                </a>
                            @elseif($comment)
                                <button type="button" class="btn btn-sm btn-outline-primary" title="@lang('marketplace::admin.reports.view_comment')" data-bs-toggle="modal" data-bs-target="#reportedComment{{ $report->id }}">
                                    <i class="bi bi-eye" aria-hidden="true"></i><span class="visually-hidden">@lang('marketplace::admin.reports.view_comment')</span>
                                </button>
                            @else
                                <span class="badge bg-secondary">@lang('marketplace::admin.reports.removed')</span>
                            @endif
                        </td>
                    </tr>

                @empty
                    <tr><td colspan="5" class="text-center py-5"><i class="bi bi-check-circle fs-1 text-success d-block mb-2" aria-hidden="true"></i><strong class="d-block">@lang('marketplace::admin.reports.empty')</strong><span class="text-muted">@lang('marketplace::admin.reports.empty_help')</span></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@foreach($reports as $report)
    @php($comment = $report->reportable_type === 'marketplace.comments' ? $report->reportable : null)
    @if($comment)
        <div class="modal fade" id="reportedComment{{ $report->id }}" tabindex="-1" aria-labelledby="reportedCommentLabel{{ $report->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h2 class="modal-title fs-5" id="reportedCommentLabel{{ $report->id }}">@lang('marketplace::admin.reports.comment_details')</h2>
                            <small class="text-muted">@lang('marketplace::admin.reports.comment_by', ['user' => $comment->user->name])</small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="@lang('messages.actions.close')"></button>
                    </div>
                    <div class="modal-body">
                        <dl class="row mb-3">
                            <dt class="col-sm-4">@lang('marketplace::admin.reports.resource')</dt>
                            <dd class="col-sm-8 mb-sm-0">
                                <a href="{{ route('marketplace.resources.show', $comment->resource) }}">{{ $comment->resource->name }}</a>
                            </dd>
                        </dl>
                        <div class="rounded border bg-body-tertiary p-3" style="white-space: pre-wrap; overflow-wrap: anywhere;">{{ $comment->content }}</div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('messages.actions.close')</button>
                        @can('marketplace.delete-comments')
                            <div class="d-flex flex-wrap justify-content-end gap-2">
                                <form method="POST" action="{{ route('marketplace.comments.user.destroy', $comment->user) }}" class="marketplace-report-delete-form" data-confirm-message="{{ trans('marketplace::messages.confirm.delete_user_comments', ['user' => $comment->user->name]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="bi bi-person-x me-1" aria-hidden="true"></i>@lang('marketplace::messages.moderation.delete_user_comments')
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('marketplace.comments.destroy', $comment) }}" class="marketplace-report-delete-form" data-confirm-message="{{ trans('marketplace::messages.confirm.delete_comment') }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="bi bi-trash me-1" aria-hidden="true"></i>@lang('marketplace::messages.moderation.delete_comment')
                                    </button>
                                </form>
                            </div>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach

@if($reports->hasPages())<div class="mt-4">{{ $reports->links() }}</div>@endif
@endsection

@push('footer-scripts')
<script>
    document.querySelectorAll('.marketplace-report-delete-form').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (! window.confirm(form.dataset.confirmMessage)) {
                event.preventDefault();
            }
        });
    });
</script>
@endpush
