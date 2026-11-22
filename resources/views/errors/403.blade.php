@extends('layouts.guest')
@section('title', 'No access')
@section('content')
<div class="stage" style="grid-template-columns:1fr;justify-items:center;text-align:center">
    <div class="glass auth-card">
        <x-icon name="lock" :size="44"/>
        <h2 style="margin-top:12px">You can't open this page</h2>
        <p class="sub">{{ $exception->getMessage() ?: "Your role doesn't have access to this page." }}</p>
        <a class="btn" href="{{ auth()->check() ? route('dashboard') : route('login') }}">Back to home</a>
    </div>
</div>
@endsection
