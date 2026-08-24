@extends('admin.layouts.admin')
@section('title',trans('marketplace::admin.settings.title'))
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

<h1>@lang('marketplace::admin.settings.title')</h1>
<form method="POST" action="{{ route('marketplace.admin.settings.update') }}">@csrf @method('PUT')<div class="form-check form-switch mb-3"><input class="form-check-input" type="checkbox" name="moderation" value="1" id="moderation" @checked(setting('marketplace.moderation',true))><label class="form-check-label" for="moderation">@lang('marketplace::admin.settings.moderation')</label></div><div class="mb-3"><label class="form-label">@lang('marketplace::admin.settings.max_file_size')</label><input type="number" name="max_file_size" class="form-control" value="{{ old('max_file_size',setting('marketplace.max_file_size',51200)) }}" required><small class="text-muted">KB</small></div><button class="btn btn-primary">@lang('messages.actions.save')</button></form>
@endsection
