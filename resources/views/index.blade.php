@extends('layouts.app')

@section('title', trans('marketplace::messages.title'))

@section('content')
<div class="container content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>
                @lang('marketplace::messages.title')
                @if($canModerate)
                    <span class="d-inline-block align-middle fs-4 text-info" tabindex="0" role="img" aria-label="@lang('marketplace::messages.moderation.index_notice')" title="@lang('marketplace::messages.moderation.index_notice')" data-bs-toggle="tooltip" data-bs-placement="right">
                        <i class="bi bi-shield-check" aria-hidden="true"></i>
                    </span>
                @endif
            </h1>
            <p class="text-muted mb-0">@lang('marketplace::messages.subtitle')</p>
        </div>
        @auth<a class="btn btn-primary" href="{{ route('marketplace.resources.create') }}"><i class="bi bi-plus-lg"></i> @lang('marketplace::messages.submit')</a>@endauth
    </div>

    <div class="row">
        <aside class="col-lg-3 mb-4"><div class="list-group">
            <a href="{{ route('marketplace.index', ['sort' => $sort]) }}" class="list-group-item list-group-item-action">@lang('marketplace::messages.all_categories')</a>
            @if($myResourcesCount > 0)
                <a href="{{ route('marketplace.resources.mine', ['sort' => $sort]) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center gap-2 @if($mine) active @endif">
                    <span><i class="bi bi-person-circle me-2"></i>@lang('marketplace::messages.my_resources')</span>
                    <span class="badge rounded-pill {{ $mine ? 'bg-light text-dark' : 'bg-secondary' }}">{{ $myResourcesCount }}</span>
                </a>
            @endif
            @foreach($categories as $item)
                <a href="{{ route('marketplace.categories.show', ['category' => $item, 'sort' => $sort]) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center gap-2 @if(isset($category) && $category->is($item)) active @endif">
                    <span><i class="{{ $item->icon }} me-2"></i>{{ $item->name }}</span>
                    <span class="badge rounded-pill {{ isset($category) && $category->is($item) ? 'bg-light text-dark' : 'bg-secondary' }}">{{ $item->resources_count }}</span>
                </a>
            @endforeach
        </div></aside>

        <main class="col-lg-9">
            <form class="mb-4"><div class="input-group"><input class="form-control" name="search" value="{{ request('search') }}" placeholder="@lang('marketplace::messages.search')"><select class="form-select" name="sort" aria-label="@lang('marketplace::messages.sort.label')" style="max-width: 16rem;"><option value="updated" @selected($sort === 'updated')>@lang('marketplace::messages.sort.updated')</option><option value="downloads" @selected($sort === 'downloads')>@lang('marketplace::messages.sort.downloads')</option><option value="rating" @selected($sort === 'rating')>@lang('marketplace::messages.sort.rating')</option></select><button class="btn btn-outline-primary">@lang('marketplace::messages.sort.apply')</button></div></form>
            <div class="row g-3">
                @forelse($resources as $resource)
                    <div class="col-md-6"><div class="card h-100">
                        @if($resource->banner_path)<a href="{{ route('marketplace.resources.show', $resource) }}"><img src="{{ route('marketplace.resources.banner', $resource) }}" class="card-img-top" style="height: 180px; object-fit: cover;" alt="{{ $resource->name }}" loading="lazy"></a>@endif
                        <div class="card-body">
                        <div class="d-flex justify-content-between gap-2">
                            <div>
                                <span class="badge bg-secondary">{{ $resource->category->name }}</span>
                                @if($canModerate || $mine)
                                    <span class="badge bg-{{ $resource->status === 'published' ? 'success' : ($resource->status === 'pending' ? 'warning text-dark' : 'danger') }}">@lang('marketplace::messages.status_labels.'.$resource->status)</span>
                                @endif
                                @if($resource->isPaused())<span class="badge bg-warning text-dark">@lang('marketplace::messages.paused')</span>@endif
                            </div>
                            <strong>{{ $resource->price > 0 ? format_money($resource->price) : trans('marketplace::messages.free') }}</strong>
                        </div>
                        <h2 class="h5 mt-3"><a class="text-decoration-none" href="{{ route('marketplace.resources.show', $resource) }}">{{ $resource->name }}</a></h2>
                        <p>{{ $resource->summary }}</p>
                        <small class="text-muted d-block">{{ $resource->author->name }} · ★ {{ round($resource->ratings_avg_rating ?? 0, 1) }}</small>
                        @if($resource->version)<small class="text-muted">v{{ $resource->version }}@if($resource->latestUpdate) · @lang('marketplace::messages.updates.updated') {{ format_date($resource->latestUpdate->created_at) }}@endif</small>@endif
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
