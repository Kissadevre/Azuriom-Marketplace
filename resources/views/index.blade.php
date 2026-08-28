@extends('layouts.app')

@section('title', trans('marketplace::messages.title'))

@push('styles')
<style>
    .marketplace-hero { position: relative; overflow: hidden; border: 1px solid rgba(var(--bs-primary-rgb), .18); background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), .12), rgba(var(--bs-primary-rgb), .025) 62%, transparent); }
    .marketplace-hero::after { position: absolute; right: -3rem; bottom: -5rem; width: 15rem; height: 15rem; border-radius: 50%; background: rgba(var(--bs-primary-rgb), .08); content: ''; pointer-events: none; }
    .marketplace-sidebar { overflow: hidden; }
    .marketplace-sidebar .list-group-item { border-right: 0; border-left: 0; padding: .9rem 1rem; }
    .marketplace-sidebar .list-group-item:first-child { border-top: 0; }
    .marketplace-sidebar .list-group-item:last-child { border-bottom: 0; }
    .marketplace-category-icon { width: 1.6rem; text-align: center; }
    .marketplace-resource-card { overflow: hidden; transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease; }
    .marketplace-resource-card:hover { transform: translateY(-3px); border-color: rgba(var(--bs-primary-rgb), .35); box-shadow: 0 .75rem 1.75rem rgba(0, 0, 0, .09); }
    .marketplace-resource-media { position: relative; display: block; aspect-ratio: 16 / 8.5; overflow: hidden; background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), .18), var(--bs-tertiary-bg)); }
    .marketplace-resource-media img { width: 100%; height: 100%; object-fit: cover; transition: transform .3s ease; }
    .marketplace-resource-card:hover .marketplace-resource-media img { transform: scale(1.025); }
    .marketplace-resource-placeholder { height: 100%; display: grid; place-items: center; color: rgba(var(--bs-primary-rgb), .6); font-size: 3rem; }
    .marketplace-price { position: absolute; top: .8rem; right: .8rem; padding: .45rem .7rem; border: 1px solid rgba(255, 255, 255, .45); border-radius: 50rem; background: rgba(var(--bs-body-bg-rgb), .9); color: var(--bs-body-color); font-weight: 700; line-height: 1; backdrop-filter: blur(8px); }
    .marketplace-summary { display: -webkit-box; min-height: 3rem; overflow: hidden; -webkit-box-orient: vertical; -webkit-line-clamp: 2; }
    .marketplace-author { min-width: 0; }
    .marketplace-avatar { width: 2rem; height: 2rem; object-fit: cover; }
    @media (min-width: 992px) { .marketplace-sidebar-wrap { position: sticky; top: 1.5rem; } }
</style>
@endpush

@section('content')
<div class="container content">
    <section class="marketplace-hero rounded-4 p-4 p-lg-5 mb-4">
        <div class="position-relative d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4" style="z-index: 1;">
            <div>
                <div class="d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary text-white fs-3" style="width: 3.25rem; height: 3.25rem;"><i class="bi bi-shop" aria-hidden="true"></i></span>
                    <h1 class="display-6 fw-semibold mb-0">@lang('marketplace::messages.title') @if($canModerate)<span class="d-inline-block align-middle fs-4 text-info" tabindex="0" role="img" aria-label="@lang('marketplace::messages.moderation.index_notice')" title="@lang('marketplace::messages.moderation.index_notice')" data-bs-toggle="tooltip" data-bs-placement="right"><i class="bi bi-shield-check" aria-hidden="true"></i></span>@endif</h1>
                </div>
                <p class="text-muted fs-5 mt-3 mb-0">@lang('marketplace::messages.subtitle')</p>
            </div>
            @auth
                @can('marketplace.publish')
                    @if(setting('marketplace.pause_submissions', false))
                        <button class="btn btn-secondary btn-lg" type="button" disabled title="@lang('marketplace::messages.submissions_paused')"><i class="bi bi-pause-circle me-1"></i>@lang('marketplace::messages.submissions_paused_action')</button>
                    @else
                        <a class="btn btn-primary btn-lg px-4" href="{{ route('marketplace.resources.create') }}"><i class="bi bi-plus-lg me-1"></i>@lang('marketplace::messages.submit')</a>
                    @endif
                @endcan
            @endauth
        </div>
    </section>

    <div class="row g-4">
        <aside class="col-lg-3">
            <div class="marketplace-sidebar-wrap">
                <div class="card marketplace-sidebar">
                    <div class="card-header d-flex align-items-center justify-content-between py-3"><strong><i class="bi bi-grid me-2" aria-hidden="true"></i>@lang('marketplace::messages.browse')</strong><span class="badge bg-primary rounded-pill">{{ $categories->count() }}</span></div>
                    <div class="list-group list-group-flush">
                        <a href="{{ route('marketplace.index', ['sort' => $sort]) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center gap-2 @if(! isset($category) && ! $mine && ! $purchased) active @endif"><span class="text-truncate"><i class="bi bi-collection marketplace-category-icon me-2"></i>@lang('marketplace::messages.all_categories')</span><span class="badge rounded-pill {{ ! isset($category) && ! $mine && ! $purchased ? 'bg-light text-dark' : 'bg-secondary' }}">{{ $categories->sum('resources_count') }}</span></a>
                        @if($myResourcesCount > 0)
                            <a href="{{ route('marketplace.resources.mine', ['sort' => $sort]) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center gap-2 @if($mine) active @endif"><span class="text-truncate"><i class="bi bi-person-circle marketplace-category-icon me-2"></i>@lang('marketplace::messages.my_resources')</span><span class="badge rounded-pill {{ $mine ? 'bg-light text-dark' : 'bg-secondary' }}">{{ $myResourcesCount }}</span></a>
                        @endif
                        @auth
                            <a href="{{ route('marketplace.resources.purchased', ['sort' => $sort]) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center gap-2 @if($purchased) active @endif"><span class="text-truncate"><i class="bi bi-bag-check marketplace-category-icon me-2"></i>@lang('marketplace::messages.purchased_resources')</span><span class="badge rounded-pill {{ $purchased ? 'bg-light text-dark' : 'bg-secondary' }}">{{ $purchasedResourcesCount }}</span></a>
                            <a href="{{ route('marketplace.gift-codes.index') }}" class="list-group-item list-group-item-action"><i class="bi bi-gift marketplace-category-icon me-2"></i>@lang('marketplace::messages.gift_codes.title')</a>
                        @endauth
                        @foreach($categories as $item)
                            <a href="{{ route('marketplace.categories.show', ['category' => $item, 'sort' => $sort]) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center gap-2 @if(isset($category) && $category->is($item)) active @endif"><span class="text-truncate"><i class="{{ $item->icon }} marketplace-category-icon me-2"></i>{{ $item->name }}</span><span class="badge rounded-pill {{ isset($category) && $category->is($item) ? 'bg-light text-dark' : 'bg-secondary' }}">{{ $item->resources_count }}</span></a>
                        @endforeach
                    </div>
                </div>
            </div>
        </aside>

        <main class="col-lg-9">
            <div class="card mb-4"><div class="card-body p-3"><form method="GET"><div class="row g-2"><div class="col-md"><div class="input-group"><span class="input-group-text bg-transparent"><i class="bi bi-search" aria-hidden="true"></i></span><input class="form-control" name="search" value="{{ request('search') }}" placeholder="@lang('marketplace::messages.search')" aria-label="@lang('marketplace::messages.search')"></div></div><div class="col-md-auto"><select class="form-select h-100" name="sort" aria-label="@lang('marketplace::messages.sort.label')"><option value="updated" @selected($sort === 'updated')>@lang('marketplace::messages.sort.updated')</option><option value="downloads" @selected($sort === 'downloads')>@lang('marketplace::messages.sort.downloads')</option><option value="rating" @selected($sort === 'rating')>@lang('marketplace::messages.sort.rating')</option></select></div><div class="col-md-auto"><button class="btn btn-primary h-100 w-100 px-4"><i class="bi bi-funnel me-1" aria-hidden="true"></i>@lang('marketplace::messages.sort.apply')</button></div></div></form></div></div>

            <div class="d-flex justify-content-between align-items-end gap-3 mb-3"><div><h2 class="h4 mb-1">{{ $mine ? trans('marketplace::messages.my_resources') : ($purchased ? trans('marketplace::messages.purchased_resources') : ($category->name ?? trans('marketplace::messages.all_resources'))) }}</h2><p class="text-muted mb-0">@choice('marketplace::messages.results', $resources->total(), ['count' => $resources->total()])</p></div></div>

            <div class="row g-4">
                @forelse($resources as $resource)
                    <div class="col-md-6">
                        <article class="card marketplace-resource-card h-100">
                            <a href="{{ route('marketplace.resources.show', $resource) }}" class="marketplace-resource-media text-decoration-none" aria-label="{{ $resource->name }}">
                                @if($resource->banner_path)<img src="{{ route('marketplace.resources.banner', $resource) }}" alt="{{ $resource->name }}" loading="lazy">@else<span class="marketplace-resource-placeholder"><i class="bi bi-box-seam" aria-hidden="true"></i></span>@endif
                                <span class="marketplace-price">{{ $resource->price > 0 ? format_money($resource->price) : trans('marketplace::messages.free') }}</span>
                            </a>
                            <div class="card-body d-flex flex-column p-4">
                                <div class="d-flex flex-wrap gap-1 mb-3"><span class="badge bg-primary-subtle text-primary-emphasis"><i class="{{ $resource->category->icon }} me-1" aria-hidden="true"></i>{{ $resource->category->name }}</span>@if($canModerate || $mine)<span class="badge bg-{{ $resource->status === 'published' ? 'success' : ($resource->status === 'pending' ? 'warning text-dark' : 'danger') }}">@lang('marketplace::messages.status_labels.'.$resource->status)</span>@endif @if($resource->isPaused())<span class="badge bg-warning text-dark"><i class="bi bi-pause-fill" aria-hidden="true"></i>@lang('marketplace::messages.paused')</span>@endif</div>
                                <h3 class="h5 mb-2"><a class="text-body text-decoration-none" href="{{ route('marketplace.resources.show', $resource) }}">{{ $resource->name }}</a></h3>
                                <p class="marketplace-summary text-muted mb-3">{{ $resource->summary }}</p>
                                @if($resource->tags->isNotEmpty())<div class="d-flex flex-wrap gap-1 mb-3">@foreach($resource->tags as $tag)<span class="badge bg-body-secondary text-body d-inline-flex align-items-center gap-1"><span class="rounded-circle" style="width: .6rem; height: .6rem; background-color: {{ $tag->color }};"></span>{{ $tag->name }}</span>@endforeach</div>@endif
                                <div class="mt-auto border-top pt-3">
                                    <div class="d-flex justify-content-between align-items-center gap-3"><div class="marketplace-author d-flex align-items-center gap-2"><img src="{{ $resource->author->getAvatar(64) }}" class="marketplace-avatar rounded-circle" alt="" loading="lazy"><span class="small text-truncate">{{ $resource->author->name }}</span></div><div class="d-flex align-items-center gap-3 small text-muted text-nowrap"><span title="@lang('marketplace::messages.rating')"><i class="bi bi-star-fill text-warning me-1" aria-hidden="true"></i>{{ round($resource->ratings_avg_rating ?? 0, 1) }}</span><span title="@lang('marketplace::messages.downloads')"><i class="bi bi-download me-1" aria-hidden="true"></i>{{ $resource->downloads }}</span></div></div>
                                    @if($resource->version || $resource->latestUpdate)<div class="d-flex justify-content-between gap-2 mt-3 small text-muted"><span>@if($resource->version)v{{ $resource->version }}@endif</span>@if($resource->latestUpdate)<span><i class="bi bi-clock-history me-1" aria-hidden="true"></i>@lang('marketplace::messages.updates.updated') {{ format_date($resource->latestUpdate->created_at) }}</span>@endif</div>@endif
                                </div>
                            </div>
                        </article>
                    </div>
                @empty
                    <div class="col-12"><div class="card"><div class="card-body text-center py-5"><span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary fs-1 mb-3" style="width: 5rem; height: 5rem;"><i class="bi bi-search" aria-hidden="true"></i></span><h2 class="h4">@lang('marketplace::messages.empty')</h2><p class="text-muted mb-0">@lang('marketplace::messages.empty_help')</p></div></div></div>
                @endforelse
            </div>

            @if($resources->hasPages())<div class="d-flex justify-content-center mt-4">{{ $resources->links() }}</div>@endif
        </main>
    </div>
</div>
@endsection
