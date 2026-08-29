@php($items = $items ?? [])
<nav aria-label="@lang('marketplace::messages.breadcrumb.label')" class="mb-4">
    <ol class="breadcrumb mb-0">
        @if($items !== [])<li class="breadcrumb-item"><a href="{{ route('marketplace.index') }}"><i class="bi bi-shop me-1" aria-hidden="true"></i>@lang('marketplace::messages.title')</a></li>@else<li class="breadcrumb-item active" aria-current="page"><i class="bi bi-shop me-1" aria-hidden="true"></i>@lang('marketplace::messages.title')</li>@endif
        @foreach($items as $item)
            @if(! $loop->last && isset($item['url']))<li class="breadcrumb-item"><a href="{{ $item['url'] }}">{{ $item['label'] }}</a></li>@else<li class="breadcrumb-item active" aria-current="page">{{ $item['label'] }}</li>@endif
        @endforeach
    </ol>
</nav>
