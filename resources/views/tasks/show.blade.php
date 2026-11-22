@extends('layouts.app')
@section('title', $task->title)
@section('content')
@php
    $u = auth()->user();
    $isMember = $task->project->members->contains('id', $u->id);
@endphp
<div class="page-head">
    <div>
        <div class="crumbs"><a href="{{ route('tasks.index') }}">Tasks</a> / Activity history</div>
        <h1>{{ $task->title }}</h1>
        <p><x-badge :status="$task->status"/> @if ($task->isOverdue())<span class="badge b-bad">Overdue</span>@endif &nbsp; <a href="{{ route('projects.show', $task->project) }}">{{ \Illuminate\Support\Str::limit($task->project->title, 60) }}</a></p>
    </div>
</div>
<div class="grid side">
    <div class="glass card">
        <div class="card-head"><h2>Activity history</h2></div>
        @if ($task->activities->isEmpty())
            <div class="empty"><x-icon name="clock"/><div>No activity recorded yet.</div></div>
        @else
        <ul class="timeline">
            @foreach ($task->activities as $a)
                <li>
                    <span class="dot"><x-icon :name="$a->action === 'update' ? 'activity' : ($a->action === 'status' ? 'refresh' : 'plus')"/></span>
                    <b>{{ $a->label() }}</b>
                    @if ($a->detail)<div>{{ $a->detail }}</div>@endif
                    <div class="small muted">{{ $a->user->name ?? 'System' }} · {{ $a->created_at->format('M j, Y g:i A') }}</div>
                </li>
            @endforeach
        </ul>
        @endif
    </div>
    <div>
        <div class="glass card">
            <div class="card-head"><h2>Details</h2></div>
            <ul class="list">
                <li><span class="muted">Assigned to</span><b>{{ $task->assignee->name ?? 'Nobody' }}</b></li>
                <li><span class="muted">Created by</span><b>{{ $task->creator->name ?? '-' }}</b></li>
                <li><span class="muted">Due</span><b>{{ $task->due_date ? $task->due_date->format('M j, Y') : 'No date' }}</b></li>
                <li><span class="muted">Milestone</span><b>{{ optional(optional($task->milestone)->milestone)->title ?? 'None' }}</b></li>
            </ul>
            @if ($task->description)<hr class="divider"><p class="small muted" style="margin:0">{{ $task->description }}</p>@endif
        </div>
        @if ($isMember)
        <form method="POST" action="{{ route('projects.updates.store', $task->project) }}" class="glass card" data-loading>
            @csrf
            <input type="hidden" name="task_id" value="{{ $task->id }}">
            <div class="card-head"><h2>Add a progress update</h2></div>
            <div class="field"><textarea name="body" placeholder="What did you finish on this task?" required></textarea></div>
            <button class="btn" type="submit"><x-icon name="send"/> Post update</button>
        </form>
        @endif
    </div>
</div>
@endsection
