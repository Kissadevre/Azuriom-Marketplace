@extends('admin.layouts.admin')
@section('title',$category->name)
@section('content')@include('marketplace::admin._nav')<h1>{{ $category->name }}</h1><form method="POST" action="{{ route('marketplace.admin.categories.update',$category) }}">@method('PUT') @include('marketplace::admin.categories._form')</form>@endsection
