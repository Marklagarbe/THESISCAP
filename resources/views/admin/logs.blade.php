@extends('layouts.app')
@section('title', 'Activity Logs')
@section('content')
<div class="page-head"><div><h1>Activity logs</h1><p>Who did what, and when.</p></div></div>
<form method="GET" class="glass card no-print"><div class="row"><div style="flex:1;min-width:220px"><input type="search" name="q" value="{{ request('q') }}" placeholder="Search action or description"></div><button class="btn">Search</button></div></form>
<div class="glass card">
    <div class="table-wrap"><table>
        <thead><tr><th>When</th><th>User</th><th>Action</th><th>Details</th><th>IP</th></tr></thead>
        <tbody>
        @forelse ($logs as $l)
            <tr><td class="small nowrap">{{ $l->created_at->format('M j, Y g:i A') }}</td><td class="small">{{ $l->user->name ?? 'System' }}</td><td><span class="badge b-info">{{ $l->action }}</span></td><td class="small">{{ $l->description }}</td><td class="small faint">{{ $l->ip }}</td></tr>
        @empty
            <tr><td colspan="5" class="muted">No activity recorded.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    <div style="margin-top:14px">{{ $logs->links() }}</div>
</div>
@endsection
