@extends('layouts.guest')
@section('title', 'Not found')
@section('content')
<div class="stage" style="grid-template-columns:1fr;justify-items:center;text-align:center">
    <div class="glass auth-card">
        <x-icon name="search" :size="44"/>
        <h2 style="margin-top:12px">Page not found</h2>
        <p class="sub">That page doesn't exist, or you don't have access to it.</p>
        <a class="btn" href="{{ auth()->check() ? route('dashboard') : route('home') }}">Back to home</a>
    </div>
</div>
@endsection
