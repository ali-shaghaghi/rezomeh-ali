@extends('adminpanel::layouts.master')

@section('title', 'ویرایش مقاله')

@section('content')
    @livewire('admin.blog.post-form', ['post' => $post, 'locale' => app()->getLocale()])
@endsection
