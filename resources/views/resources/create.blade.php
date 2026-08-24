@extends('layouts.app')
@section('title',trans('marketplace::messages.submit'))
@section('content')<div class="container content"><h1>@lang('marketplace::messages.submit')</h1><form method="POST" action="{{ route('marketplace.resources.store') }}" enctype="multipart/form-data">@include('marketplace::resources._form')</form></div>@endsection
