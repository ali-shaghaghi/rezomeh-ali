@extends('adminpanel::layouts.master')

@section('title', 'نمایش تیکت')

@section('content')
    @livewire('admin.tickets.show', ['ticket' => $ticket])
@endsection
