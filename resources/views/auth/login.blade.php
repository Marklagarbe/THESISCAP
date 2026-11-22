@extends('layouts.guest')
@section('title', 'Sign in')
@section('content')
<div class="stage">
    @include('partials.hero')

    <div class="glass auth-card">
        <h2>Welcome back</h2>
        <p class="sub">Sign in to continue to your dashboard.</p>

        <form method="POST" action="{{ route('login.attempt') }}" data-loading novalidate>
            @csrf
            <div class="field">
                <label for="email">Email address</label>
                <div class="input">
                    <x-icon name="mail"/>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="you@school.edu" autocomplete="username" required autofocus>
                </div>
                @error('email')<div class="err">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label for="password">Password</label>
                <div class="input">
                    <x-icon name="lock"/>
                    <input id="password" type="password" name="password" placeholder="Your password" autocomplete="current-password" required>
                    @include('partials.pw-toggle', ['target' => 'password'])
                </div>
                @error('password')<div class="err">{{ $message }}</div>@enderror
            </div>
            <div class="field"><label class="check"><input type="checkbox" name="remember" value="1"> Keep me signed in</label></div>
            <button type="submit" class="btn block">Sign in</button>
        </form>

        <p class="auth-foot">New to ThesisCap? <a href="{{ route('register') }}">Create an account</a></p>

        @if (config('thesiscap.show_demo'))
            <details class="fold">
                <summary>Demo accounts</summary>
                <div class="body">
                    <p class="small muted" style="margin-bottom:4px">Click an account to fill the form. Password: <b>{{ config('thesiscap.demo_password') }}</b></p>
                    @foreach (config('thesiscap.demo_accounts') as $demo)
                        <button type="button" class="demo-btn" data-demo="{{ $demo['email'] }}" data-pass="{{ config('thesiscap.demo_password') }}">
                            <span>{{ $demo['label'] }}</span><small>{{ $demo['email'] }}</small>
                        </button>
                    @endforeach
                </div>
            </details>
        @endif
    </div>
</div>
@endsection
