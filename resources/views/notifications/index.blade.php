@extends('layouts.app')
@section('title', 'Notifications')
@section('content')
<div class="page-head"><div><h1>Notifications</h1><p>Updates on your projects, tasks and submissions.</p></div>
    <form method="POST" action="{{ route('notices.readall') }}">@csrf<button class="btn ghost">Mark all as read</button></form>
</div>
<div class="glass card">
    <ul class="list">
        @forelse ($notices as $n)
            <li>
                <div class="grow"><a href="{{ route('notices.open', $n) }}"><b>{{ $n->title }}</b></a>@if ($n->body)<span class="small muted">{{ $n->body }}</span>@endif<span class="small faint">{{ $n->created_at->diffForHumans() }}</span></div>
                @if (! $n->read_at)<span class="badge b-info">New</span>@endif
            </li>
        @empty
            <li class="empty" style="display:block"><x-icon name="bell"/><div>You're all caught up.</div></li>
        @endforelse
    </ul>
    <div style="margin-top:14px">{{ $notices->links() }}</div>
</div>
@endsection
