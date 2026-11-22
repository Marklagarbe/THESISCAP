@extends('layouts.app')
@section('title', 'Coordinator Dashboard')
@section('content')
<div class="page-head">
    <div><h1>Project Coordinator dashboard</h1><p>Every registered project, across every department.</p></div>
    <div class="row"><a class="btn ghost" href="{{ route('coordinator.assignments') }}"><x-icon name="users"/> Assignments</a><a class="btn" href="{{ route('coordinator.reports') }}"><x-icon name="chart"/> Accomplishment report</a></div>
</div>
<div class="stats">
    <div class="glass stat"><div class="lbl">Registered projects</div><div class="num">{{ $projects->count() }}</div></div>
    <div class="glass stat good"><div class="lbl">Completed</div><div class="num">{{ $projects->where('status', 'completed')->count() }}</div></div>
    <div class="glass stat {{ $delayed->count() ? 'hot' : '' }}"><div class="lbl">Delayed</div><div class="num">{{ $delayed->count() }}</div></div>
    <div class="glass stat {{ $unassigned->count() ? 'hot' : '' }}"><div class="lbl">Missing instructor or adviser</div><div class="num">{{ $unassigned->count() }}</div></div>
</div>
<div class="grid side">
    <div class="glass card">
        <div class="card-head"><h2>Overall project progress</h2><a href="{{ route('projects.index') }}">All projects</a></div>
        @include('partials.project-table', ['projects' => $projects])
    </div>
    <div>
        <div class="glass card">
            <div class="card-head"><h2>By department</h2></div>
            @foreach ($byDept as $row)
                <div style="margin-bottom:14px"><div class="row between small"><b>{{ $row['dept']->code }}</b><span class="muted">{{ $row['count'] }} project(s)</span></div><x-progress :value="$row['avg']"/></div>
            @endforeach
        </div>
        <div class="glass card">
            <div class="card-head"><h2>Delayed projects</h2><a href="{{ route('coordinator.delayed') }}">See all</a></div>
            <ul class="list">
                @forelse ($delayed->take(5) as $p)
                    <li><div class="grow"><a href="{{ route('projects.show', $p) }}"><b>{{ \Illuminate\Support\Str::limit($p->title, 36) }}</b></a><span class="small muted">{{ $p->delayedMilestones()->count() }} overdue milestone(s)</span></div><x-badge status="delayed"/></li>
                @empty
                    <li class="muted">No delayed projects.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
