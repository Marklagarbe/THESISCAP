@extends('layouts.app')
@section('title', 'Manager Home')
@section('content')
<div class="page-head">
    <div>
        <h1>Project Manager home</h1>
        <p>Keep your group on schedule: members, tasks, deadlines and submissions.</p>
    </div>
    <div class="row">
        <a class="btn ghost" href="{{ route('projects.create') }}"><x-icon name="plus"/> New project</a>
        <a class="btn" href="{{ route('tasks.index') }}"><x-icon name="check"/> Assign tasks</a>
    </div>
</div>

@forelse ($projects as $project)
    @php
        $tasks = $project->tasks;
        $overdue = $tasks->filter->isOverdue()->count();
    @endphp
    <div class="glass card">
        <div class="card-head">
            <div><h2>{{ $project->title }}</h2><div class="small muted">{{ $project->department->code ?? '' }} · {{ ucfirst($project->type) }} · Adviser: {{ $project->adviser->name ?? 'not assigned' }} · Instructor: {{ $project->instructor->name ?? 'not assigned' }}</div></div>
            <div class="row"><x-badge :status="$project->statusLabel()"/><a class="btn sm ghost" href="{{ route('projects.show', $project) }}">Open</a></div>
        </div>
        <x-progress :value="$project->progress()"/>
    </div>

    <div class="stats">
        <div class="glass stat"><div class="lbl">Group members</div><div class="num">{{ $project->members->count() }}</div></div>
        <div class="glass stat"><div class="lbl">Open tasks</div><div class="num">{{ $tasks->where('status', '!=', 'done')->count() }}</div></div>
        <div class="glass stat {{ $overdue ? 'hot' : '' }}"><div class="lbl">Overdue tasks</div><div class="num">{{ $overdue }}</div></div>
        <div class="glass stat good"><div class="lbl">Tasks done</div><div class="num">{{ $tasks->where('status', 'done')->count() }}</div></div>
    </div>

    <div class="grid two">
        <div class="glass card">
            <div class="card-head"><h2>Member progress</h2><a href="{{ route('projects.show', $project) }}#members">Manage members</a></div>
            <div class="table-wrap"><table>
                <thead><tr><th>Member</th><th>Tasks</th><th style="width:40%">Progress</th></tr></thead>
                <tbody>
                @foreach ($project->members as $m)
                    @php($mine = $tasks->where('assigned_to', $m->id))
                    @php($pct = $mine->count() ? (int) round($mine->where('status', 'done')->count() / $mine->count() * 100) : 0)
                    <tr><td><div class="row"><x-avatar :user="$m"/><b>{{ $m->name }}</b></div></td><td>{{ $mine->where('status', 'done')->count() }}/{{ $mine->count() }}</td><td><x-progress :value="$pct"/></td></tr>
                @endforeach
                </tbody>
            </table></div>
        </div>
        <div class="glass card">
            <div class="card-head"><h2>Upcoming group deadlines</h2></div>
            @php($mine = $deadlines->where('project_id', $project->id))
            <ul class="list">
                @forelse ($mine as $d)
                    <li><div class="grow"><b>{{ $d->milestone->title }}</b><span class="small muted">Due {{ $d->due_date ? $d->due_date->format('M j, Y') : 'no date' }}</span></div>@if ($d->isDelayed())<span class="badge b-bad">{{ $d->daysLate() }}d late</span>@else<x-badge :status="$d->status"/>@endif</li>
                @empty
                    <li class="muted">Every milestone is approved.</li>
                @endforelse
            </ul>
        </div>
    </div>
@empty
    <div class="glass card empty"><x-icon name="folder"/><h3>Register your group's project</h3><p>Create the project first, then add members and start assigning tasks.</p><a class="btn" href="{{ route('projects.create') }}">Register project</a></div>
@endforelse

<div class="glass card">
    <div class="card-head"><h2>Group submissions</h2><a href="{{ route('submissions.index') }}">View all</a></div>
    @include('partials.submission-table', ['submissions' => $submissions])
</div>
@endsection
