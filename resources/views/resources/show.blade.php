@extends('layouts.app')

@section('title', $resource->name)

@push('styles')
<style>
    .marketplace-resource-content img { max-width: 100%; height: auto; }
    .marketplace-resource-content table { width: 100%; margin-bottom: 1rem; border-collapse: collapse; }
    .marketplace-resource-content th, .marketplace-resource-content td { padding: .5rem; border: 1px solid var(--bs-border-color); }
    .marketplace-resource-content pre { padding: 1rem; overflow: auto; border-radius: .375rem; background: var(--bs-tertiary-bg); }
    .marketplace-download-banner { width: 100%; aspect-ratio: 16 / 9; object-fit: cover; }
    .marketplace-author-banner { width: 88px; height: 58px; flex: 0 0 88px; object-fit: cover; }
    .marketplace-moderation-menu { min-width: 14rem; }
</style>
@endpush

@section('content')
@php($updatesTabActive = $errors->hasAny(['version', 'description', 'file', 'external_url']))
<div class="container content">
    <div class="row g-4">
        <main class="col-lg-8">
            <div class="d-flex justify-content-between gap-3">
                <div>
                    <span class="badge bg-secondary">{{ $resource->category->name }}</span>
                    <span class="badge bg-{{ $resource->status === 'published' ? 'success' : ($resource->status === 'pending' ? 'warning text-dark' : 'danger') }}">@lang('marketplace::messages.status_labels.'.$resource->status)</span>
                    @if($resource->isPaused())<span class="badge bg-warning text-dark">@lang('marketplace::messages.paused')</span>@endif
                    <h1 class="mt-2">{{ $resource->name }}</h1>
                    <p class="text-muted">@lang('marketplace::messages.by') {{ $resource->author->name }} @if($resource->version) · v{{ $resource->version }} @endif</p>
                </div>

                @auth
                    <div class="d-flex gap-2 align-self-start">
                        @if(! $resource->isOwnedBy(auth()->user()))
                            <button class="btn btn-outline-danger marketplace-report-action" type="button" data-report-url="{{ route('marketplace.resources.report', $resource) }}" data-report-subject="@lang('marketplace::messages.reports.resource_subject', ['resource' => $resource->name])" title="@lang('marketplace::messages.reports.action')">
                                <i class="bi bi-flag" aria-hidden="true"></i><span class="visually-hidden">@lang('marketplace::messages.reports.action')</span>
                            </button>
                        @endif
                        @if($resource->isOwnedBy(auth()->user()) || auth()->user()->can('marketplace.edit'))
                            <a href="{{ route('marketplace.resources.edit', $resource) }}" class="btn btn-outline-secondary">@lang('messages.actions.edit')</a>
                        @endif

                        @if($resource->isOwnedBy(auth()->user()) || (auth()->user()->can('marketplace.moderate') && $resource->status === 'pending') || auth()->user()->canAny(['marketplace.archive', 'marketplace.pause', 'marketplace.delete', 'marketplace.reset-ratings']))
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" title="@lang('marketplace::messages.moderation.menu')">
                                    <i class="bi bi-shield-check" aria-hidden="true"></i><span class="visually-hidden">@lang('marketplace::messages.moderation.menu')</span>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end marketplace-moderation-menu">
                                    @if(auth()->user()->can('marketplace.moderate') && $resource->status === 'pending')
                                        <form method="POST" action="{{ route('marketplace.resources.approve', $resource) }}">@csrf @method('PATCH')<button class="dropdown-item text-success marketplace-confirm-action" type="button" data-confirm-message="@lang('marketplace::messages.confirm.approve')"><i class="bi bi-check-circle me-2" aria-hidden="true"></i>@lang('marketplace::messages.moderation.approve')</button></form>
                                        <form method="POST" action="{{ route('marketplace.resources.reject', $resource) }}">@csrf @method('PATCH')<input type="hidden" name="moderation_note"><button class="dropdown-item text-danger marketplace-confirm-action" type="button" data-confirm-message="@lang('marketplace::messages.confirm.reject')" data-confirm-reason="true"><i class="bi bi-x-circle me-2" aria-hidden="true"></i>@lang('marketplace::messages.moderation.reject')</button></form>
                                    @endif
                                    @can('marketplace.pause')
                                        <form method="POST" action="{{ $resource->isPaused() ? route('marketplace.resources.resume', $resource) : route('marketplace.resources.pause', $resource) }}">@csrf @method('PATCH')<button class="dropdown-item text-warning marketplace-confirm-action" type="button" data-confirm-message="@lang($resource->isPaused() ? 'marketplace::messages.confirm.resume' : 'marketplace::messages.confirm.pause')"><i class="bi bi-{{ $resource->isPaused() ? 'play-circle' : 'pause-circle' }} me-2" aria-hidden="true"></i>@lang($resource->isPaused() ? 'marketplace::messages.moderation.resume' : 'marketplace::messages.moderation.pause')</button></form>
                                    @endcan
                                    @can('marketplace.reset-ratings')
                                        <form method="POST" action="{{ route('marketplace.resources.ratings.reset', $resource) }}">@csrf @method('DELETE')<button class="dropdown-item marketplace-confirm-action" type="button" data-confirm-message="@lang('marketplace::messages.confirm.reset_ratings')"><i class="bi bi-star me-2" aria-hidden="true"></i>@lang('marketplace::messages.moderation.reset_ratings')</button></form>
                                    @endcan
                                    @can('marketplace.archive')
                                        <form method="POST" action="{{ route('marketplace.resources.archive', $resource) }}">@csrf @method('PATCH')<button class="dropdown-item text-danger marketplace-confirm-action" type="button" data-confirm-message="@lang('marketplace::messages.confirm.archive')"><i class="bi bi-archive me-2" aria-hidden="true"></i>@lang('marketplace::messages.moderation.archive')</button></form>
                                    @endcan
                                    @if($resource->isOwnedBy(auth()->user()) || auth()->user()->can('marketplace.delete'))
                                        <form method="POST" action="{{ route('marketplace.resources.destroy', $resource) }}" class="border-top">@csrf @method('DELETE')<button class="dropdown-item text-danger marketplace-confirm-action" type="button" data-confirm-message="@lang('marketplace::messages.confirm.delete_resource')"><i class="bi bi-trash me-2" aria-hidden="true"></i>@lang('marketplace::messages.moderation.delete_resource')</button></form>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                @endauth
            </div>

            @if($resource->status !== 'published')
                <div class="alert alert-warning">@lang('marketplace::messages.status.'.$resource->status) @if($resource->moderation_note)<hr>{{ $resource->moderation_note }}@endif</div>
            @endif
            @if($resource->isPaused())<div class="alert alert-warning"><i class="bi bi-pause-circle me-2"></i>@lang('marketplace::messages.pause_notice')</div>@endif

            <ul class="nav nav-tabs mb-4" role="tablist">
                <li class="nav-item" role="presentation"><button class="nav-link @if(! $updatesTabActive) active @endif" id="general-tab" data-bs-toggle="tab" data-bs-target="#general-pane" type="button" role="tab" aria-controls="general-pane" aria-selected="{{ $updatesTabActive ? 'false' : 'true' }}">@lang('marketplace::messages.tabs.general')</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link @if($updatesTabActive) active @endif" id="updates-tab" data-bs-toggle="tab" data-bs-target="#updates-pane" type="button" role="tab" aria-controls="updates-pane" aria-selected="{{ $updatesTabActive ? 'true' : 'false' }}">@lang('marketplace::messages.tabs.updates') <span class="badge bg-secondary ms-1">{{ $resource->updates->count() }}</span></button></li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade @if(! $updatesTabActive) show active @endif" id="general-pane" role="tabpanel" aria-labelledby="general-tab" tabindex="0">
                    <div class="card mb-4"><div class="card-body"><p class="lead">{{ $resource->summary }}</p><div class="marketplace-resource-content">{!! $resource->description !!}</div></div></div>

                    <h2 class="h4">@lang('marketplace::messages.comments')</h2>
                    @if(setting('marketplace.pause_comments', false))
                        <div class="alert alert-warning"><i class="bi bi-chat-square-x me-2" aria-hidden="true"></i>@lang('marketplace::messages.comments_paused')</div>
                    @else
                    @auth
                        @if(! $resource->isPaused() && $resource->isUnlockedBy(auth()->user()) && $resource->status === 'published')
                            <form method="POST" action="{{ route('marketplace.comments.store', $resource) }}" class="mb-4">@csrf<textarea name="content" class="form-control mb-2" rows="3" maxlength="5000" required></textarea><button class="btn btn-primary">@lang('marketplace::messages.comment')</button></form>
                        @endif
                    @endauth
                    @endif

                    @forelse($resource->comments as $comment)
                        <div class="card mb-2"><div class="card-body"><div class="d-flex justify-content-between gap-2"><div><strong>{{ $comment->user->name }}</strong><small class="text-muted ms-2">{{ format_date($comment->created_at, true) }}</small></div>
                            @auth
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="@lang('marketplace::messages.comment_actions')">
                                        <i class="bi bi-three-dots-vertical" aria-hidden="true"></i><span class="visually-hidden">@lang('marketplace::messages.comment_actions')</span>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        @if($comment->user_id === auth()->id() || auth()->user()->can('marketplace.delete-comments'))
                                            <form method="POST" action="{{ route('marketplace.comments.destroy', $comment) }}">
                                                @csrf @method('DELETE')
                                                <button class="dropdown-item text-danger marketplace-confirm-action" type="button" data-confirm-message="@lang('marketplace::messages.confirm.delete_comment')"><i class="bi bi-trash me-2" aria-hidden="true"></i>@lang('marketplace::messages.moderation.delete_comment')</button>
                                            </form>
                                            @if(auth()->user()->can('marketplace.delete-comments'))
                                                <form method="POST" action="{{ route('marketplace.comments.user.destroy', $comment->user) }}">
                                                    @csrf @method('DELETE')
                                                    <button class="dropdown-item text-danger marketplace-confirm-action" type="button" data-confirm-message="@lang('marketplace::messages.confirm.delete_user_comments', ['user' => $comment->user->name])"><i class="bi bi-person-x me-2" aria-hidden="true"></i>@lang('marketplace::messages.moderation.delete_user_comments')</button>
                                                </form>
                                            @endif
                                        @endif
                                        @if($comment->user_id !== auth()->id())
                                            <button class="dropdown-item text-danger marketplace-report-action" type="button" data-report-url="{{ route('marketplace.comments.report', $comment) }}" data-report-subject="@lang('marketplace::messages.reports.comment_subject', ['user' => $comment->user->name])"><i class="bi bi-flag me-2" aria-hidden="true"></i>@lang('marketplace::messages.reports.action')</button>
                                        @endif
                                    </div>
                                </div>
                            @endauth
                        </div><p class="mb-0 mt-2">{{ $comment->content }}</p></div></div>
                    @empty
                        <p class="text-muted">@lang('marketplace::messages.no_comments')</p>
                    @endforelse
                </div>

                <div class="tab-pane fade @if($updatesTabActive) show active @endif" id="updates-pane" role="tabpanel" aria-labelledby="updates-tab" tabindex="0">
                    <div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h4 mb-0">@lang('marketplace::messages.updates.title')</h2>@if($resource->latestUpdate)<small class="text-muted">@lang('marketplace::messages.updates.last_update') {{ format_date($resource->latestUpdate->created_at, true) }}</small>@endif</div>
                    @auth
                        @if($resource->isOwnedBy(auth()->user()) || auth()->user()->can('marketplace.edit'))
                            <div class="card border-primary mb-4"><div class="card-header">@lang('marketplace::messages.updates.publish')</div><div class="card-body"><form method="POST" action="{{ route('marketplace.resources.updates.store', $resource) }}" enctype="multipart/form-data">@csrf
                                <div class="mb-3"><label class="form-label" for="updateVersion">@lang('marketplace::messages.updates.version')</label><input id="updateVersion" name="version" class="form-control @error('version') is-invalid @enderror" value="{{ old('version') }}" maxlength="30" required placeholder="{{ $resource->version ? 'v'.$resource->version.' →' : '1.0.0' }}">@error('version')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
                                <div class="mb-3"><label class="form-label" for="updateDescription">@lang('marketplace::messages.updates.changelog')</label><textarea id="updateDescription" name="description" class="form-control @error('description') is-invalid @enderror" rows="5" maxlength="10000" required>{{ old('description') }}</textarea>@error('description')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
                                @if($resource->delivery_type === 'file')<div class="mb-3"><label class="form-label" for="updateFile">@lang('marketplace::messages.updates.file')</label><input id="updateFile" type="file" name="file" class="form-control @error('file') is-invalid @enderror" accept="{{ app(\Azuriom\Plugin\Marketplace\Support\ResourceFilePolicy::class)->acceptAttribute() }}" required>@error('file')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
                                @else<div class="mb-3"><label class="form-label" for="updateUrl">@lang('marketplace::messages.updates.url')</label><input id="updateUrl" type="url" name="external_url" class="form-control @error('external_url') is-invalid @enderror" value="{{ old('external_url', $resource->external_url) }}" required>@error('external_url')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>@endif
                                <button class="btn btn-primary"><i class="bi bi-cloud-arrow-up me-1"></i>@lang('marketplace::messages.updates.publish_action')</button>
                            </form></div></div>
                        @endif
                    @endauth

                    @forelse($resource->updates as $update)
                        <article class="card mb-3"><div class="card-body"><div class="d-flex justify-content-between align-items-start gap-3"><div><h3 class="h5 mb-1">v{{ $update->version }}</h3><small class="text-muted">@lang('marketplace::messages.updates.by', ['user' => $update->author->name])</small></div><time class="text-muted" datetime="{{ $update->created_at->toIso8601String() }}">{{ format_date($update->created_at, true) }}</time></div><div class="mt-3" style="white-space: pre-wrap">{{ $update->description }}</div></div></article>
                    @empty
                        <p class="text-muted">@lang('marketplace::messages.updates.empty')</p>
                    @endforelse
                </div>
            </div>
        </main>

        <aside class="col-lg-4">
            <div class="card mb-3 overflow-hidden">
                @if($resource->banner_path)<img src="{{ route('marketplace.resources.banner', $resource) }}" class="marketplace-download-banner" alt="{{ $resource->name }}">@endif
                <div class="card-body text-center"><div class="display-6">{{ $resource->price > 0 ? format_money($resource->price) : trans('marketplace::messages.free') }}</div><p>★ {{ $resource->averageRating() }} · {{ $resource->downloads }} @lang('marketplace::messages.downloads')</p>
                    @auth
                        @if($resource->isPaused())<button class="btn btn-secondary w-100" disabled>@lang('marketplace::messages.paused')</button>
                        @elseif($resource->status === 'published' && ($resource->isUnlockedBy(auth()->user()) || auth()->user()->can('marketplace.download-paid')))<a class="btn btn-success w-100 mb-3" href="{{ route('marketplace.resources.download', $resource) }}">@lang('marketplace::messages.get_resource')</a>@if($resource->isUnlockedBy(auth()->user()))<form method="POST" action="{{ route('marketplace.ratings.store', $resource) }}">@csrf<div class="input-group"><select name="rating" class="form-select">@for($i = 5; $i >= 1; $i--)<option value="{{ $i }}">{{ $i }} ★</option>@endfor</select><button class="btn btn-outline-primary">@lang('marketplace::messages.rate')</button></div></form>@endif
                        @elseif($resource->status === 'published')<form method="POST" action="{{ route('marketplace.resources.purchase', $resource) }}">@csrf<button class="btn btn-primary w-100">@lang('marketplace::messages.unlock')</button></form>@endif
                    @else
                        @if($resource->isPaused())<button class="btn btn-secondary w-100" disabled>@lang('marketplace::messages.paused')</button>
                        @elseif($resource->status === 'published' && $resource->price <= 0 && ! setting('marketplace.require_login_for_free_downloads', true))<a class="btn btn-success w-100" href="{{ route('marketplace.resources.download', $resource) }}">@lang('marketplace::messages.get_resource')</a>
                        @else<a href="{{ route('login') }}" class="btn btn-primary w-100">@lang('marketplace::messages.login_to_download')</a>@endif
                    @endauth
                </div>
            </div>

            @if($relatedResources->isNotEmpty())
                <div class="card"><div class="card-header"><strong>@lang('marketplace::messages.author_resources', ['user' => $resource->author->name])</strong></div><div class="list-group list-group-flush">
                    @foreach($relatedResources as $related)
                        <a href="{{ route('marketplace.resources.show', $related) }}" class="list-group-item list-group-item-action d-flex gap-3 align-items-center">
                            @if($related->banner_path)<img src="{{ route('marketplace.resources.banner', $related) }}" class="rounded marketplace-author-banner" alt="" loading="lazy">@else<div class="rounded marketplace-author-banner bg-body-secondary d-flex align-items-center justify-content-center"><i class="bi bi-box"></i></div>@endif
                            <span class="min-w-0"><strong class="d-block text-truncate">{{ $related->name }}</strong><small class="text-muted">{{ $related->category->name }} @if($related->version)· v{{ $related->version }}@endif</small></span>
                        </a>
                    @endforeach
                </div></div>
            @endif
        </aside>
    </div>
</div>

@auth
    <div class="modal fade" id="marketplaceConfirmModal" tabindex="-1" aria-labelledby="marketplaceConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="marketplaceConfirmModalLabel">@lang('marketplace::messages.confirm.title')</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="@lang('marketplace::messages.confirm.cancel')"></button>
                </div>
                <div class="modal-body">
                    <p id="marketplaceConfirmMessage" class="mb-0"></p>
                    <div id="marketplaceConfirmReasonGroup" class="mt-3 d-none">
                        <label class="form-label" for="marketplaceConfirmReason">@lang('marketplace::messages.moderation.reason')</label>
                        <textarea id="marketplaceConfirmReason" class="form-control" rows="4" maxlength="2000"></textarea>
                        <div class="invalid-feedback">@lang('marketplace::messages.confirm.reason_required')</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('marketplace::messages.confirm.cancel')</button>
                    <button type="button" class="btn btn-danger" id="marketplaceConfirmSubmit">@lang('marketplace::messages.confirm.action')</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="marketplaceReportModal" tabindex="-1" aria-labelledby="marketplaceReportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" id="marketplaceReportForm" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="marketplaceReportModalLabel">@lang('marketplace::messages.reports.title')</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="@lang('marketplace::messages.confirm.cancel')"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">@lang('marketplace::messages.reports.message')</p>
                    <div class="alert alert-light border" id="marketplaceReportSubject"></div>
                    <label class="form-label" for="marketplaceReportReason">@lang('marketplace::messages.reports.reason')</label>
                    <textarea id="marketplaceReportReason" name="reason" class="form-control" rows="5" minlength="5" maxlength="2000" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('marketplace::messages.confirm.cancel')</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-flag me-1" aria-hidden="true"></i>@lang('marketplace::messages.reports.submit')</button>
                </div>
            </form>
        </div>
    </div>
@endauth
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modalElement = document.getElementById('marketplaceConfirmModal');

        if (! modalElement) {
            return;
        }

        const modal = new bootstrap.Modal(modalElement);
        const message = document.getElementById('marketplaceConfirmMessage');
        const reasonGroup = document.getElementById('marketplaceConfirmReasonGroup');
        const reason = document.getElementById('marketplaceConfirmReason');
        const submit = document.getElementById('marketplaceConfirmSubmit');
        let actionForm = null;

        document.querySelectorAll('.marketplace-confirm-action').forEach((button) => {
            button.addEventListener('click', () => {
                actionForm = button.closest('form');
                message.textContent = button.dataset.confirmMessage;
                reasonGroup.classList.toggle('d-none', button.dataset.confirmReason !== 'true');
                reason.value = '';
                reason.classList.remove('is-invalid');
                modal.show();
            });
        });

        submit.addEventListener('click', () => {
            if (! actionForm) {
                return;
            }

            const reasonInput = actionForm.querySelector('input[name="moderation_note"]');

            if (reasonInput && reason.value.trim() === '') {
                reason.classList.add('is-invalid');
                reason.focus();
                return;
            }

            if (reasonInput) {
                reasonInput.value = reason.value.trim();
            }

            submit.disabled = true;
            actionForm.submit();
        });

        modalElement.addEventListener('hidden.bs.modal', () => {
            actionForm = null;
            submit.disabled = false;
        });

        const reportModalElement = document.getElementById('marketplaceReportModal');
        const reportModal = new bootstrap.Modal(reportModalElement);
        const reportForm = document.getElementById('marketplaceReportForm');
        const reportSubject = document.getElementById('marketplaceReportSubject');
        const reportReason = document.getElementById('marketplaceReportReason');

        document.querySelectorAll('.marketplace-report-action').forEach((button) => {
            button.addEventListener('click', () => {
                reportForm.action = button.dataset.reportUrl;
                reportSubject.textContent = button.dataset.reportSubject;
                reportReason.value = '';
                reportModal.show();
            });
        });

        reportModalElement.addEventListener('shown.bs.modal', () => reportReason.focus());
    });
</script>
@endpush
