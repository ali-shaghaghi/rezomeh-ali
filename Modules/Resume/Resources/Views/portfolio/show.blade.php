@extends('adminpanel::layouts.master')

@section('title', $project->title)

@section('content')
    @livewire('admin.resume.portfolio-show', ['project' => $project])
@endsection
