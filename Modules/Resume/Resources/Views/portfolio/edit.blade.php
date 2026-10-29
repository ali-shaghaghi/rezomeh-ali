@extends('adminpanel::layouts.master')

@section('title', 'ویرایش: ' . $project->title)

@section('content')
    @livewire('admin.resume.portfolio-form', ['project' => $project])
@endsection
