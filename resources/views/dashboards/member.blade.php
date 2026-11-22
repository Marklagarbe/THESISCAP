@extends('layouts.app')
@section('title', 'Member Home')
@section('content')
@php($project = $projects->first())
<div class="page-head">
    <div>
        <h1>Hi, {{ explode(' ', auth()->user()->name)[0] }}</h1>
        <p>Your thesis workspace: what's next, what's due and what your adviser said.</p>
    </div>
    <a class="btn" href="{{ route('submissions.create') }}"><x-icon name="upload"/> Submit a requirement</a>
</div>

@if (! $project)
    <div class="glass card empty"><x-icon name="folder"/><h3>You are not in a project yet</h3><p>Ask your Student Project Manager to add you using your account email: <b>{{ auth()->user()->email }}</b></p></div>
@else
<div class="stats">
    <div class="glass stat"><div class="lbl">Project progress</div><div class="num">{{ $project->progress() }}%</div><div class="lbl">{{ $project->milestones->where('status', 'approved')->count() }} of {{ $project->milestones->count() }} milestones approved</div></div>
    <div class="glass stat"><div class="lbl">My task progress</div><div class="num">{{ $personal }}%</div><div class="lbl">{{ $done }} of {{ $tasks->count() }} tasks done</div></div>
    <div class="glass stat {{ $tasks->filter->isOverdue()->count() ? 'hot' : '' }}"><div class="lbl">Overdue tasks</div><div class="num">{{ $tasks->filter->isOverdue()->count() }}</div><div class="lbl">Finish these first</div></div>
    <div class="glass stat"><div class="lbl">Open feedback</div><div class="num">{{ $feedback->whereIn('kind', ['revision_request', 'recommendation'])->count() }}</div><div class="lbl">Revisions and recommendations</div></div>
</div>

<div class="grid side">
    <div class="glass card">
        <div class="card-head"><h2>Project milestones</h2><a href="{{ route('projects.show', $project) }}">Open project</a></div>
        <p class="muted small">{{ $project->title }}</p>
        @include('partials.timeline', ['project' => $project, 'actions' => false])
    </div>
    <div>
        <div class="glass card">
            <div class="card-head"><h2>My tasks</h2><a href="{{ route('tasks.index') }}">All tasks</a></div>
            @include('partials.task-list', ['tasks' => $tasks->take(5), 'emptyText' => 'No tasks assigned to you.'])
        </div>
        <div class="glass card">
            <div class="card-head"><h2>Feedback and revisions</h2></div>
            @forelse ($feedback as $f)
                <div style="padding:10px 0;border-bottom:1px solid var(--line)">
                    <div class="row between"><b class="small">{{ $f->user->name ?? 'Staff' }}</b><span class="badge b-{{ $f->kind === 'revision_request' ? 'warn' : ($f->kind === 'approval' ? 'ok' : 'info') }}">{{ $f->kindLabel() }}</span></div>
                    <div class="small muted">on <a href="{{ route('submissions.show', $f->submission) }}">{{ $f->submission->title }}</a></div>
                    @if ($f->body)<div class="small" style="margin-top:4px">{{ $f->body }}</div>@endif
                </div>
            @empty
                <div class="empty">No feedback yet. It will show up here.</div>
            @endforelse
        </div>
    </div>
</div>
@endif
@endsection
