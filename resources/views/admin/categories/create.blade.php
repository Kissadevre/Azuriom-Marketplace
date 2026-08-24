@extends('admin.layouts.admin')
@section('title',trans('messages.actions.add'))
@section('content')<h1>@lang('messages.actions.add')</h1><form method="POST" action="{{ route('marketplace.admin.categories.store') }}">@include('marketplace::admin.categories._form')</form>@endsection
