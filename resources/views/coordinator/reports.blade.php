@extends('layouts.app')
@section('title', 'Accomplishment Report')
@section('content')
<div class="page-head">
    <div><h1>Accomplishment report</h1><p>Generated {{ now()->format('F j, Y') }}</p></div>
    <div class="row no-print">
        <button class="btn ghost" onclick="window.print()"><x-icon name="file"/> Print</button>
        <a class="btn" href="{{ route('coordinator.export', request()->query()) }}"><x-icon name="download"/> Export CSV</a>
    </div>
</div>
<form method="GET" class="glass card no-print"><div class="row"><div style="flex:1;min-width:200px"><select name="department"><option value="">All departments</option>@foreach ($departments as $d)<option value="{{ $d->id }}" @selected(request('department') == $d->id)>{{ $d->code }}: {{ $d->name }}</option>@endforeach</select></div><button class="btn">Apply</button></div></form>
<div class="stats">
    <div class="glass stat"><div class="lbl">Projects</div><div class="num">{{ $summary['total'] }}</div></div>
    <div class="glass stat good"><div class="lbl">Completed</div><div class="num">{{ $summary['completed'] }}</div></div>
    <div class="glass stat {{ $summary['delayed'] ? 'hot' : '' }}"><div class="lbl">Delayed</div><div class="num">{{ $summary['delayed'] }}</div></div>
    <div class="glass stat"><div class="lbl">Average progress</div><div class="num">{{ $summary['avg'] }}%</div></div>
</div>
<div class="glass card">
    <div class="table-wrap"><table>
        <thead><tr><th>Project</th><th>Dept</th><th>Leader</th><th>Instructor</th><th>Adviser</th><th>Milestones</th><th style="width:16%">Progress</th><th>Status</th></tr></thead>
        <tbody>
        @forelse ($rows as $p)
            <tr>
                <td><a class="title" href="{{ route('projects.show', $p) }}">{{ \Illuminate\Support\Str::limit($p->title, 40) }}</a><span class="small muted">{{ ucfirst($p->type) }} · {{ $p->members->count() }} member(s)</span></td>
                <td>{{ $p->department->code ?? '-' }}</td><td class="small">{{ $p->leader->name ?? '-' }}</td><td class="small">{{ $p->instructor->name ?? '-' }}</td><td class="small">{{ $p->adviser->name ?? '-' }}</td>
                <td>{{ $p->milestones->where('status', 'approved')->count() }}/{{ $p->milestones->count() }}</td>
                <td><x-progress :value="$p->progress()"/></td><td><x-badge :status="$p->statusLabel()"/></td>
            </tr>
        @empty
            <tr><td colspan="8" class="muted">No projects for this filter.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div>
@endsection
