@extends('adminpanel::layouts.master')

@section('title', 'افزودن مقاله جدید')

@section('content')
    @livewire('admin.blog.post-form', ['post' => null, 'locale' => 'fa'])
@endsection
