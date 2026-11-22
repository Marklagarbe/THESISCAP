<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | ThesisCap</title>
    @include('partials.head')
</head>
<body>
@php($me = auth()->user())
@php($unread = $me->unreadNoticeCount())
<div class="topbar">
    <button id="menu-btn" aria-label="Open menu"><x-icon name="menu" :size="26"/></button>
    <span class="brand sm"><x-icon name="cap"/> ThesisCap</span>
    <a href="{{ route('notices.index') }}" aria-label="Notifications"><x-icon name="bell" :size="22"/></a>
</div>
<div class="shell">
    <aside class="sidebar glass">
        <a href="{{ route('dashboard') }}" class="brand sm"><x-icon name="cap"/> ThesisCap</a>
        <nav class="nav" aria-label="Main">
            @foreach (\App\Support\Nav::for($me) as [$label, $url, $icon, $match])
                <a href="{{ $url }}" class="{{ request()->is($match) ? 'active' : '' }}">
                    <x-icon :name="$icon"/> {{ $label }}
                    @if ($label === 'Notifications' && $unread > 0)<span class="count">{{ $unread }}</span>@endif
                </a>
            @endforeach
        </nav>
        <div class="me">
            <x-avatar :user="$me"/>
            <div><b>{{ $me->name }}</b><span>{{ $me->roleLabel() }}@if($me->department) · {{ $me->department->code }}@endif</span></div>
        </div>
        <form method="POST" action="{{ route('logout') }}" style="margin-top:10px">
            @csrf
            <button class="btn ghost sm" style="width:100%"><x-icon name="logout"/> Sign out</button>
        </form>
    </aside>

    <main class="main">
        @if (session('success'))<div class="flash ok"><x-icon name="tick"/> {{ session('success') }}</div>@endif
        @if ($errors->any())
            <div class="flash bad"><x-icon name="alert"/> <div>@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div></div>
        @endif
        @yield('content')
    </main>
</div>
<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
