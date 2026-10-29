@extends('adminpanel::layouts.master')

@section('title', 'نمایش سفارش')

@section('content')
    @livewire('admin.orders.show', ['order' => $order])
@endsection
