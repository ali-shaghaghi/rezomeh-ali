@extends('adminpanel::layouts.master')

@section('title', 'تیکت‌های باز')

@section('content')
    @livewire('admin.tickets.index', ['initialStatus' => 'open'])
@endsection
