@extends('adminpanel::layouts.master')

@section('title', 'مقالات وبلاگ')

@section('content')
    @livewire('admin.blog.posts-index')
@endsection
