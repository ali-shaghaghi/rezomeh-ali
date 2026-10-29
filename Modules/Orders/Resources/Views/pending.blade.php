@extends('adminpanel::layouts.master')

@section('title', 'سفارشات در انتظار')

@section('content')
    @livewire('admin.orders.index', ['initialStatus' => 'pending'])
@endsection
