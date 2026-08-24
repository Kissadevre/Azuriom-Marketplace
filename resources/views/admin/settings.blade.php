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

<div class="d-flex align-items-center gap-3 mb-3">
    <span class="d-flex align-items-center justify-content-center rounded bg-primary bg-opacity-10 text-primary fs-3" style="width: 3rem; height: 3rem;"><i class="bi bi-sliders" aria-hidden="true"></i></span>
    <div><h1 class="h3 mb-1">@lang('marketplace::admin.settings.title')</h1><p class="text-muted mb-0">@lang('marketplace::admin.settings.description')</p></div>
</div>

<form method="POST" action="{{ route('marketplace.admin.settings.update') }}">
    @csrf
    @method('PUT')
    <div class="card mb-4">
        <div class="card-header"><strong>@lang('marketplace::admin.settings.general')</strong></div>
        <div class="card-body p-0">
            <div class="d-flex align-items-center justify-content-between gap-4 p-4 border-bottom">
                <label for="moderation" class="mb-0">
                    <span class="d-block fw-semibold">@lang('marketplace::admin.settings.moderation')</span>
                    <small class="text-muted">@lang('marketplace::admin.settings.moderation_help')</small>
                </label>
                <div class="form-check form-switch fs-4 mb-0"><input class="form-check-input" type="checkbox" name="moderation" value="1" id="moderation" @checked(setting('marketplace.moderation', true))></div>
            </div>
            <div class="d-flex align-items-center justify-content-between gap-4 p-4 border-bottom">
                <label for="pauseSubmissions" class="mb-0">
                    <span class="d-block fw-semibold">@lang('marketplace::admin.settings.pause_submissions')</span>
                    <small class="text-muted">@lang('marketplace::admin.settings.pause_submissions_help')</small>
                </label>
                <div class="form-check form-switch fs-4 mb-0"><input class="form-check-input" type="checkbox" name="pause_submissions" value="1" id="pauseSubmissions" @checked(setting('marketplace.pause_submissions', false))></div>
            </div>
            <div class="d-flex align-items-center justify-content-between gap-4 p-4">
                <label for="pauseComments" class="mb-0">
                    <span class="d-block fw-semibold">@lang('marketplace::admin.settings.pause_comments')</span>
                    <small class="text-muted">@lang('marketplace::admin.settings.pause_comments_help')</small>
                </label>
                <div class="form-check form-switch fs-4 mb-0"><input class="form-check-input" type="checkbox" name="pause_comments" value="1" id="pauseComments" @checked(setting('marketplace.pause_comments', false))></div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><strong>@lang('marketplace::admin.settings.files')</strong></div>
        <div class="card-body">
            <label class="form-label fw-semibold" for="maxFileSize">@lang('marketplace::admin.settings.max_file_size')</label>
            <div class="input-group" style="max-width: 32rem;"><input id="maxFileSize" type="number" min="1" max="1048576" name="max_file_size" class="form-control @error('max_file_size') is-invalid @enderror" value="{{ old('max_file_size', setting('marketplace.max_file_size', 51200)) }}" required><span class="input-group-text">KB</span>@error('max_file_size')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
            <small class="form-text text-muted">@lang('marketplace::admin.settings.max_file_size_help')</small>
        </div>
    </div>

    <div class="text-end"><button class="btn btn-primary px-4"><i class="bi bi-check-lg me-1" aria-hidden="true"></i>@lang('messages.actions.save')</button></div>
</form>
@endsection
