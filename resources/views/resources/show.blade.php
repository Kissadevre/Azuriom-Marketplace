@extends('layouts.app')

@section('title', $resource->name)

@section('content')
<div class="container content">
    <div class="row">
        <main class="col-lg-8">
            <div class="d-flex justify-content-between gap-3">
                <div>
                    <span class="badge bg-secondary">{{ $resource->category->name }}</span>
                    <span class="badge bg-{{ $resource->status === 'published' ? 'success' : ($resource->status === 'pending' ? 'warning text-dark' : 'danger') }}">
                        @lang('marketplace::messages.status_labels.'.$resource->status)
                    </span>
                    @if($resource->isPaused())
                        <span class="badge bg-warning text-dark">@lang('marketplace::messages.paused')</span>
                    @endif
                    <h1 class="mt-2">{{ $resource->name }}</h1>
                    <p class="text-muted">
                        @lang('marketplace::messages.by') {{ $resource->author->name }}
                        @if($resource->version) · v{{ $resource->version }} @endif
                    </p>
                </div>
                @auth
                    @if($resource->isOwnedBy(auth()->user()) || auth()->user()->can('marketplace.edit'))
                        <a href="{{ route('marketplace.resources.edit', $resource) }}" class="btn btn-outline-secondary align-self-start">
                            @lang('messages.actions.edit')
                        </a>
                    @endif
                @endauth
            </div>

            @if($resource->status !== 'published')
                <div class="alert alert-warning">
                    @lang('marketplace::messages.status.'.$resource->status)
                    @if($resource->moderation_note)<hr>{{ $resource->moderation_note }}@endif
                </div>
            @endif
            @if($resource->isPaused())
                <div class="alert alert-warning"><i class="bi bi-pause-circle me-2"></i>@lang('marketplace::messages.pause_notice')</div>
            @endif

            @auth
                @if(auth()->user()->can('marketplace.moderate') && $resource->status === 'pending')
                    <div class="card border-warning mb-4">
                        <div class="card-header">@lang('marketplace::messages.moderation.review')</div>
                        <div class="card-body">
                            <div class="d-flex flex-column gap-2">
                                <form method="POST" action="{{ route('marketplace.resources.approve', $resource) }}">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-success">@lang('marketplace::messages.moderation.approve')</button>
                                </form>
                                <form method="POST" action="{{ route('marketplace.resources.reject', $resource) }}" class="d-flex gap-2">
                                    @csrf @method('PATCH')
                                    <input name="moderation_note" class="form-control" maxlength="2000" required placeholder="@lang('marketplace::messages.moderation.reason')">
                                    <button class="btn btn-danger">@lang('marketplace::messages.moderation.reject')</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            @endauth

            <div class="card mb-4"><div class="card-body">
                <p class="lead">{{ $resource->summary }}</p>
                <div style="white-space: pre-wrap">{{ $resource->description }}</div>
            </div></div>

            <h2 class="h4">@lang('marketplace::messages.comments')</h2>
            @auth
                @if(! $resource->isPaused() && $resource->isUnlockedBy(auth()->user()) && $resource->status === 'published')
                    <form method="POST" action="{{ route('marketplace.comments.store', $resource) }}" class="mb-4">
                        @csrf
                        <textarea name="content" class="form-control mb-2" rows="3" maxlength="5000" required></textarea>
                        <button class="btn btn-primary">@lang('marketplace::messages.comment')</button>
                    </form>
                @endif
            @endauth

            @forelse($resource->comments as $comment)
                <div class="card mb-2"><div class="card-body">
                    <div class="d-flex justify-content-between gap-2">
                        <div><strong>{{ $comment->user->name }}</strong><small class="text-muted ms-2">{{ format_date($comment->created_at, true) }}</small></div>
                        @auth
                            @if($comment->user_id === auth()->id() || auth()->user()->can('marketplace.delete-comments'))
                                <div class="d-flex gap-1">
                                    <form method="POST" action="{{ route('marketplace.comments.destroy', $comment) }}" onsubmit="return confirm(@js(trans('marketplace::messages.confirm.delete_comment')))" >
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">@lang('marketplace::messages.moderation.delete_comment')</button>
                                    </form>
                                    @if(auth()->user()->can('marketplace.delete-comments'))
                                        <form method="POST" action="{{ route('marketplace.comments.user.destroy', $comment->user) }}" onsubmit="return confirm(@js(trans('marketplace::messages.confirm.delete_user_comments', ['user' => $comment->user->name])))">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-danger">@lang('marketplace::messages.moderation.delete_user_comments')</button>
                                        </form>
                                    @endif
                                </div>
                            @endif
                        @endauth
                    </div>
                    <p class="mb-0 mt-2">{{ $comment->content }}</p>
                </div></div>
            @empty
                <p class="text-muted">@lang('marketplace::messages.no_comments')</p>
            @endforelse
        </main>

        <aside class="col-lg-4">
            <div class="card mb-3"><div class="card-body text-center">
                <div class="display-6">{{ $resource->price > 0 ? format_money($resource->price) : trans('marketplace::messages.free') }}</div>
                <p>★ {{ $resource->averageRating() }} · {{ $resource->downloads }} @lang('marketplace::messages.downloads')</p>
                @auth
                    @if($resource->isPaused())
                        <button class="btn btn-secondary w-100" disabled>@lang('marketplace::messages.paused')</button>
                    @elseif($resource->status === 'published' && ($resource->isUnlockedBy(auth()->user()) || auth()->user()->can('marketplace.download-paid')))
                        <a class="btn btn-success w-100 mb-3" href="{{ route('marketplace.resources.download', $resource) }}">@lang('marketplace::messages.get_resource')</a>
                        @if($resource->isUnlockedBy(auth()->user()))
                            <form method="POST" action="{{ route('marketplace.ratings.store', $resource) }}">
                                @csrf
                                <div class="input-group"><select name="rating" class="form-select">@for($i = 5; $i >= 1; $i--)<option value="{{ $i }}">{{ $i }} ★</option>@endfor</select><button class="btn btn-outline-primary">@lang('marketplace::messages.rate')</button></div>
                            </form>
                        @endif
                    @elseif($resource->status === 'published')
                        <form method="POST" action="{{ route('marketplace.resources.purchase', $resource) }}">@csrf<button class="btn btn-primary w-100">@lang('marketplace::messages.unlock')</button></form>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary w-100">@lang('auth.login')</a>
                @endauth
            </div></div>

            @auth
                @if($resource->isOwnedBy(auth()->user()) || auth()->user()->canAny(['marketplace.archive', 'marketplace.pause', 'marketplace.delete', 'marketplace.reset-ratings']))
                    <div class="card border-danger">
                        <div class="card-header">@lang('marketplace::messages.moderation.tools')</div>
                        <div class="card-body d-grid gap-2">
                            @can('marketplace.pause')
                                <form method="POST" action="{{ $resource->isPaused() ? route('marketplace.resources.resume', $resource) : route('marketplace.resources.pause', $resource) }}">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-outline-warning w-100">@lang($resource->isPaused() ? 'marketplace::messages.moderation.resume' : 'marketplace::messages.moderation.pause')</button>
                                </form>
                            @endcan
                            @can('marketplace.reset-ratings')
                                <form method="POST" action="{{ route('marketplace.resources.ratings.reset', $resource) }}" onsubmit="return confirm(@js(trans('marketplace::messages.confirm.reset_ratings')))" >
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-secondary w-100">@lang('marketplace::messages.moderation.reset_ratings')</button>
                                </form>
                            @endcan
                            @can('marketplace.archive')
                                <form method="POST" action="{{ route('marketplace.resources.archive', $resource) }}" onsubmit="return confirm(@js(trans('marketplace::messages.confirm.archive')))" >
                                    @csrf @method('PATCH')
                                    <button class="btn btn-outline-danger w-100">@lang('marketplace::messages.moderation.archive')</button>
                                </form>
                            @endcan
                            @if($resource->isOwnedBy(auth()->user()) || auth()->user()->can('marketplace.delete'))
                                <form method="POST" action="{{ route('marketplace.resources.destroy', $resource) }}" onsubmit="return confirm(@js(trans('marketplace::messages.confirm.delete_resource')))" >
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger w-100">@lang('marketplace::messages.moderation.delete_resource')</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endif
            @endauth
        </aside>
    </div>
</div>
@endsection
