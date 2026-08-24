@extends('layouts.app')

@section('title', trans('marketplace::messages.external_warning.title'))

@section('content')
<div class="container content">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card border-warning">
                <div class="card-body p-4 p-md-5 text-center">
                    <i class="bi bi-box-arrow-up-right display-3 text-warning" aria-hidden="true"></i>
                    <h1 class="h3 mt-3">@lang('marketplace::messages.external_warning.title')</h1>
                    <p class="lead">@lang('marketplace::messages.external_warning.message')</p>
                    <p class="mb-4">
                        @lang('marketplace::messages.external_warning.destination')
                        <strong class="d-block mt-1">{{ $destinationHost }}</strong>
                    </p>
                    <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                        <form method="POST" action="{{ route('marketplace.resources.external', $resource) }}">
                            @csrf
                            <button type="submit" class="btn btn-warning">
                                @lang('marketplace::messages.external_warning.continue')
                            </button>
                        </form>
                        <a href="{{ route('marketplace.resources.show', $resource) }}" class="btn btn-outline-secondary">
                            @lang('marketplace::messages.external_warning.cancel')
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
