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
            <div class="d-flex align-items-center justify-content-between gap-4 p-4 border-bottom">
                <label for="pauseComments" class="mb-0">
                    <span class="d-block fw-semibold">@lang('marketplace::admin.settings.pause_comments')</span>
                    <small class="text-muted">@lang('marketplace::admin.settings.pause_comments_help')</small>
                </label>
                <div class="form-check form-switch fs-4 mb-0"><input class="form-check-input" type="checkbox" name="pause_comments" value="1" id="pauseComments" @checked(setting('marketplace.pause_comments', false))></div>
            </div>
            <div class="d-flex align-items-center justify-content-between gap-4 p-4">
                <label for="requireLoginForFreeDownloads" class="mb-0">
                    <span class="d-block fw-semibold">@lang('marketplace::admin.settings.require_login_for_free_downloads')</span>
                    <small class="text-muted">@lang('marketplace::admin.settings.require_login_for_free_downloads_help')</small>
                </label>
                <div class="form-check form-switch fs-4 mb-0"><input class="form-check-input" type="checkbox" name="require_login_for_free_downloads" value="1" id="requireLoginForFreeDownloads" @checked(setting('marketplace.require_login_for_free_downloads', true))></div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><strong><i class="bi bi-speedometer2 me-2" aria-hidden="true"></i>@lang('marketplace::admin.rate_limits.title')</strong></div>
        <div class="card-body">
            <p class="text-muted">@lang('marketplace::admin.rate_limits.help')</p>
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="rateLimitPublish">@lang('marketplace::admin.rate_limits.publish')</label>
                    <div class="input-group"><input id="rateLimitPublish" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="5" data-integer-only name="rate_limit_publish" class="form-control @error('rate_limit_publish') is-invalid @enderror" value="{{ old('rate_limit_publish', setting('marketplace.rate_limit_publish', 300)) }}" required><span class="input-group-text">@lang('marketplace::admin.rate_limits.seconds')</span>@error('rate_limit_publish')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
                    <small class="form-text text-muted">@lang('marketplace::admin.rate_limits.publish_help')</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="rateLimitEdit">@lang('marketplace::admin.rate_limits.edit')</label>
                    <div class="input-group"><input id="rateLimitEdit" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="5" data-integer-only name="rate_limit_edit" class="form-control @error('rate_limit_edit') is-invalid @enderror" value="{{ old('rate_limit_edit', setting('marketplace.rate_limit_edit', 60)) }}" required><span class="input-group-text">@lang('marketplace::admin.rate_limits.seconds')</span>@error('rate_limit_edit')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
                    <small class="form-text text-muted">@lang('marketplace::admin.rate_limits.edit_help')</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="rateLimitUpdate">@lang('marketplace::admin.rate_limits.update')</label>
                    <div class="input-group"><input id="rateLimitUpdate" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="5" data-integer-only name="rate_limit_update" class="form-control @error('rate_limit_update') is-invalid @enderror" value="{{ old('rate_limit_update', setting('marketplace.rate_limit_update', 300)) }}" required><span class="input-group-text">@lang('marketplace::admin.rate_limits.seconds')</span>@error('rate_limit_update')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
                    <small class="form-text text-muted">@lang('marketplace::admin.rate_limits.update_help')</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="rateLimitComment">@lang('marketplace::admin.rate_limits.comment')</label>
                    <div class="input-group"><input id="rateLimitComment" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="5" data-integer-only name="rate_limit_comment" class="form-control @error('rate_limit_comment') is-invalid @enderror" value="{{ old('rate_limit_comment', setting('marketplace.rate_limit_comment', 15)) }}" required><span class="input-group-text">@lang('marketplace::admin.rate_limits.seconds')</span>@error('rate_limit_comment')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
                    <small class="form-text text-muted">@lang('marketplace::admin.rate_limits.comment_help')</small>
                </div>
            </div>
            <div class="alert alert-info mt-4 mb-0"><i class="bi bi-info-circle me-2" aria-hidden="true"></i>@lang('marketplace::admin.rate_limits.disabled_help')</div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><strong>@lang('marketplace::admin.settings.files')</strong></div>
        <div class="card-body">
            <div class="mb-4">
                <label class="form-label fw-semibold" for="maxFileSize">@lang('marketplace::admin.settings.max_file_size')</label>
                <div class="input-group" style="max-width: 32rem;"><input id="maxFileSize" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="7" data-integer-only name="max_file_size" class="form-control @error('max_file_size') is-invalid @enderror" value="{{ old('max_file_size', setting('marketplace.max_file_size', 51200)) }}" required><span class="input-group-text">KB</span>@error('max_file_size')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
                <small class="form-text text-muted">@lang('marketplace::admin.settings.max_file_size_help')</small>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="maxEditorImageSize">@lang('marketplace::admin.settings.max_editor_image_size')</label>
                    <div class="input-group"><input id="maxEditorImageSize" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="5" data-integer-only name="max_editor_image_size" class="form-control @error('max_editor_image_size') is-invalid @enderror" value="{{ old('max_editor_image_size', setting('marketplace.max_editor_image_size', 5120)) }}" required><span class="input-group-text">KB</span>@error('max_editor_image_size')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
                    <small class="form-text text-muted">@lang('marketplace::admin.settings.max_editor_image_size_help')</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="maxEditorImages">@lang('marketplace::admin.settings.max_editor_images')</label>
                    <input id="maxEditorImages" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="3" data-integer-only name="max_editor_images" class="form-control @error('max_editor_images') is-invalid @enderror" value="{{ old('max_editor_images', setting('marketplace.max_editor_images', 20)) }}" required>
                    @error('max_editor_images')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    <small class="form-text text-muted">@lang('marketplace::admin.settings.max_editor_images_help')</small>
                </div>
            </div>

            <div>
                <label class="form-label fw-semibold" for="allowedExtensions">@lang('marketplace::admin.settings.allowed_extensions')</label>
                <textarea id="allowedExtensions" name="allowed_extensions" class="form-control font-monospace @error('allowed_extensions') is-invalid @enderror" rows="3" required>{{ old('allowed_extensions', $allowedExtensions) }}</textarea>
                @error('allowed_extensions')<span class="invalid-feedback">{{ $message }}</span>@enderror
                <small class="form-text text-muted">@lang('marketplace::admin.settings.allowed_extensions_help')</small>
                <div class="alert alert-danger mt-3 mb-0">
                    <i class="bi bi-shield-exclamation me-2" aria-hidden="true"></i>
                    <strong>@lang('marketplace::admin.settings.blocked_extensions')</strong>
                    <div class="small mt-2 text-break">{{ collect($forbiddenExtensions)->map(fn ($extension) => '.'.$extension)->implode(', ') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="text-end"><button class="btn btn-primary px-4"><i class="bi bi-check-lg me-1" aria-hidden="true"></i>@lang('messages.actions.save')</button></div>
</form>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('[data-integer-only]').forEach((input) => {
        input.addEventListener('input', () => {
            input.value = input.value.replace(/[^0-9]/g, '');
        });
    });
</script>
@endpush
