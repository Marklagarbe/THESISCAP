@extends('layouts.app')
@section('title', 'Admin Dashboard')
@section('content')
<div class="page-head"><div><h1>Administrator dashboard</h1><p>Accounts, access and system health at a glance.</p></div><a class="btn" href="{{ route('admin.users') }}"><x-icon name="users"/> Manage users</a></div>
<div class="stats">
    <div class="glass stat"><div class="lbl">User accounts</div><div class="num">{{ $counts['users'] }}</div></div>
    <div class="glass stat"><div class="lbl">Projects</div><div class="num">{{ $counts['projects'] }}</div></div>
    <div class="glass stat"><div class="lbl">Submissions</div><div class="num">{{ $counts['submissions'] }}</div></div>
    <div class="glass stat {{ $counts['inactive'] ? 'hot' : '' }}"><div class="lbl">Disabled accounts</div><div class="num">{{ $counts['inactive'] }}</div></div>
</div>
<div class="grid two">
    <div class="glass card">
        <div class="card-head"><h2>Accounts by role</h2><a href="{{ route('admin.roles') }}">Roles & permissions</a></div>
        <ul class="list">@foreach ($byRole as $r)<li><span>{{ $r['label'] }}</span><b>{{ $r['count'] }}</b></li>@endforeach</ul>
    </div>
    <div class="glass card">
        <div class="card-head"><h2>Recent activity</h2><a href="{{ route('admin.logs') }}">All logs</a></div>
        <ul class="list">
            @forelse ($logs as $l)
                <li><div class="grow"><b class="small">{{ $l->user->name ?? 'System' }}</b><span class="small muted">{{ $l->description ?: $l->action }}</span></div><span class="small faint nowrap">{{ $l->created_at->diffForHumans() }}</span></li>
            @empty
                <li class="muted">No activity yet.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
