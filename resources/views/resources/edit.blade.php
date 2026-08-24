@extends('layouts.app')
@section('title',trans('messages.actions.edit'))
@section('content')<div class="container content"><h1>{{ $resource->name }}</h1><form method="POST" action="{{ route('marketplace.resources.update',$resource) }}" enctype="multipart/form-data">@method('PUT') @include('marketplace::resources._form')</form></div>@endsection
