@extends('admin.layouts.admin')

@section('title', trans('marketplace::admin.reports.title'))

@section('content')
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
                        $targetResource = $isResource ? $report->reportable : $report->reportable?->resource;
                    @endphp
                    <tr>
                        <td style="min-width: 16rem;">
                            <span class="badge {{ $isResource ? 'bg-primary' : 'bg-info text-dark' }} mb-1">@lang($isResource ? 'marketplace::admin.reports.resource' : 'marketplace::admin.reports.comment')</span>
                            <strong class="d-block">{{ $report->subject }}</strong>
                            @if($report->excerpt)<small class="text-muted d-block text-truncate" style="max-width: 28rem;">{{ $report->excerpt }}</small>@endif
                        </td>
                        <td>{{ $report->reporter->name }}</td>
                        <td style="min-width: 18rem; white-space: pre-wrap;">{{ $report->reason }}</td>
                        <td class="text-nowrap">{{ format_date($report->created_at, true) }}</td>
                        <td class="text-end">
                            @if($targetResource)
                                <a href="{{ route('marketplace.resources.show', $targetResource) }}" class="btn btn-sm btn-outline-primary" title="@lang('marketplace::admin.reports.view')" data-bs-toggle="tooltip">
                                    <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i><span class="visually-hidden">@lang('marketplace::admin.reports.view')</span>
                                </a>
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

@if($reports->hasPages())<div class="mt-4">{{ $reports->links() }}</div>@endif
@endsection
