@extends('adminpanel::layouts.master')

@section('title', 'نمایش مقاله')

@section('content')
    @livewire('admin.blog.post-show', ['post' => $post])
@endsection
