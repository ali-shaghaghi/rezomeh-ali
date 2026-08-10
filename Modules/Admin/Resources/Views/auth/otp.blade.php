@extends('admin::layouts.auth')

@section('title', 'تایید هویت')

@section('content')
<article class="auth-card w-full max-w-md p-6 sm:p-8 rounded-3xl border bg-surface-950/90 backdrop-blur-xl shadow-2xl animate-card-enter">
    <livewire:admin.auth.otp-verification />
</article>
@endsection
