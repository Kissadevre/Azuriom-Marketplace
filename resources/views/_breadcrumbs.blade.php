@php($admin = $admin ?? false)
@php($items = $items ?? [])
<nav aria-label="@lang('marketplace::messages.breadcrumb.label')" class="mb-4">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ $admin ? route('admin.dashboard') : route('home') }}"><i class="bi bi-house-door me-1" aria-hidden="true"></i>@lang($admin ? 'marketplace::messages.breadcrumb.administration' : 'marketplace::messages.breadcrumb.home')</a></li>
        @if($admin || $items !== [])<li class="breadcrumb-item"><a href="{{ $admin ? route('marketplace.admin.settings.edit') : route('marketplace.index') }}">@lang('marketplace::messages.title')</a></li>@else<li class="breadcrumb-item active" aria-current="page">@lang('marketplace::messages.title')</li>@endif
        @foreach($items as $item)
            @if(! $loop->last && isset($item['url']))<li class="breadcrumb-item"><a href="{{ $item['url'] }}">{{ $item['label'] }}</a></li>@else<li class="breadcrumb-item active" aria-current="page">{{ $item['label'] }}</li>@endif
        @endforeach
    </ol>
</nav>
