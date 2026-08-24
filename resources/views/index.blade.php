@extends('layouts.app')

@section('title', trans('marketplace::messages.title'))

@section('content')
<div class="container content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1>@lang('marketplace::messages.title')</h1><p class="text-muted mb-0">@lang('marketplace::messages.subtitle')</p></div>
        @auth<a class="btn btn-primary" href="{{ route('marketplace.resources.create') }}"><i class="bi bi-plus-lg"></i> @lang('marketplace::messages.submit')</a>@endauth
    </div>

    @if($canModerate)
        <div class="alert alert-info"><i class="bi bi-shield-check me-2"></i>@lang('marketplace::messages.moderation.index_notice')</div>
    @endif

    <div class="row">
        <aside class="col-lg-3 mb-4"><div class="list-group">
            <a href="{{ route('marketplace.index') }}" class="list-group-item list-group-item-action">@lang('marketplace::messages.all_categories')</a>
            @foreach($categories as $item)
                <a href="{{ route('marketplace.categories.show', $item) }}" class="list-group-item list-group-item-action @if(isset($category) && $category->is($item)) active @endif"><i class="{{ $item->icon }} me-2"></i>{{ $item->name }}</a>
            @endforeach
        </div></aside>

        <main class="col-lg-9">
            <form class="mb-4"><div class="input-group"><input class="form-control" name="search" value="{{ request('search') }}" placeholder="@lang('marketplace::messages.search')"><button class="btn btn-outline-primary"><i class="bi bi-search"></i></button></div></form>
            <div class="row g-3">
                @forelse($resources as $resource)
                    <div class="col-md-6"><div class="card h-100"><div class="card-body">
                        <div class="d-flex justify-content-between gap-2">
                            <div>
                                <span class="badge bg-secondary">{{ $resource->category->name }}</span>
                                @if($canModerate)
                                    <span class="badge bg-{{ $resource->status === 'published' ? 'success' : ($resource->status === 'pending' ? 'warning text-dark' : 'danger') }}">@lang('marketplace::messages.status_labels.'.$resource->status)</span>
                                @endif
                                @if($resource->isPaused())<span class="badge bg-warning text-dark">@lang('marketplace::messages.paused')</span>@endif
                            </div>
                            <strong>{{ $resource->price > 0 ? format_money($resource->price) : trans('marketplace::messages.free') }}</strong>
                        </div>
                        <h2 class="h5 mt-3"><a class="text-decoration-none" href="{{ route('marketplace.resources.show', $resource) }}">{{ $resource->name }}</a></h2>
                        <p>{{ $resource->summary }}</p>
                        <small class="text-muted">{{ $resource->author->name }} · ★ {{ round($resource->ratings_avg_rating ?? 0, 1) }}</small>
                    </div></div></div>
                @empty
                    <div class="col"><div class="alert alert-info">@lang('marketplace::messages.empty')</div></div>
                @endforelse
            </div>
            <div class="mt-4">{{ $resources->links() }}</div>
        </main>
    </div>
</div>
@endsection
