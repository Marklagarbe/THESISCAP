@extends('layouts.app')
@section('title', $project->title)
@section('content')
@php
    $u = auth()->user();
    $isLeader = $u->id === $project->leader_id;
    $canEdit = $isLeader || $u->hasPerm('assignments.manage') || $u->role === 'admin';
@endphp
<div class="page-head">
    <div>
        <div class="crumbs"><a href="{{ route('projects.index') }}">Projects</a> / {{ $project->department->code ?? '' }}</div>
        <h1>{{ $project->title }}</h1>
        <p><x-badge :status="$project->type"/> <x-badge :status="$project->statusLabel()"/> &nbsp; Leader: {{ $project->leader->name ?? '-' }}</p>
    </div>
    <div style="min-width:260px"><div class="small muted" style="margin-bottom:6px">Overall progress</div><x-progress :value="$project->progress()"/></div>
</div>

<div class="tabs no-print">
    @foreach (['overview' => 'Overview', 'milestones' => 'Milestones', 'tasks' => 'Tasks', 'submissions' => 'Submissions', 'updates' => 'Progress updates', 'timeline' => 'Timeline', 'members' => 'Members'] as $k => $l)
        <button type="button" data-tab="{{ $k }}">{{ $l }}</button>
    @endforeach
</div>

{{-- Overview --}}
<div class="tab-panel" id="tab-overview">
    <div class="grid side">
        <div class="glass card">
            <div class="card-head"><h2>About this project</h2></div>
            @if ($canEdit)
                <form method="POST" action="{{ route('projects.update', $project) }}">
                    @csrf @method('PUT')
                    <div class="field"><label for="title">Title</label><input id="title" type="text" name="title" value="{{ old('title', $project->title) }}" required></div>
                    <div class="field"><label for="abstract">Description</label><textarea id="abstract" name="abstract">{{ old('abstract', $project->abstract) }}</textarea></div>
                    <div class="field"><label for="due_date">Target completion</label><input id="due_date" type="date" name="due_date" value="{{ old('due_date', optional($project->due_date)->format('Y-m-d')) }}"></div>
                    <button class="btn sm">Save changes</button>
                </form>
            @else
                <p>{{ $project->abstract ?: 'No description yet.' }}</p>
                <p class="muted small">Target completion: {{ $project->due_date ? $project->due_date->format('M j, Y') : 'not set' }}</p>
            @endif
        </div>
        <div class="glass card">
            <div class="card-head"><h2>Team</h2></div>
            <ul class="list">
                <li><span class="muted">Department</span><b>{{ $project->department->name ?? '-' }}</b></li>
                <li><span class="muted">Instructor</span><b>{{ $project->instructor->name ?? 'Not assigned' }}</b></li>
                <li><span class="muted">Adviser</span><b>{{ $project->adviser->name ?? 'Not assigned' }}</b></li>
                <li><span class="muted">Members</span><b>{{ $project->members->count() }}</b></li>
                <li><span class="muted">Started</span><b>{{ $project->start_date ? $project->start_date->format('M j, Y') : '-' }}</b></li>
            </ul>
        </div>
    </div>
</div>

{{-- Milestones --}}
<div class="tab-panel" id="tab-milestones" hidden>
    <div class="glass card">
        <div class="card-head"><h2>Phases and milestones</h2></div>
        @include('partials.timeline', ['project' => $project, 'actions' => true])
    </div>
</div>

{{-- Tasks --}}
<div class="tab-panel" id="tab-tasks" hidden>
    <div class="glass card">
        <div class="card-head"><h2>Group tasks</h2>@if ($isLeader)<a class="btn sm" href="{{ route('tasks.index') }}">Assign a task</a>@endif</div>
        @include('partials.task-list', ['tasks' => $project->tasks, 'emptyText' => 'No tasks have been assigned.'])
    </div>
</div>

{{-- Submissions --}}
<div class="tab-panel" id="tab-submissions" hidden>
    <div class="glass card">
        <div class="card-head"><h2>Submissions</h2>@if ($u->hasPerm('submissions.upload') && $project->members->contains('id', $u->id))<a class="btn sm" href="{{ route('submissions.create') }}">Submit a file</a>@endif</div>
        @include('partials.submission-table', ['submissions' => $project->submissions->sortByDesc('created_at')->load('project', 'author')])
    </div>
</div>

{{-- Progress updates --}}
<div class="tab-panel" id="tab-updates" hidden>
    <div class="grid side">
        <div class="glass card">
            <div class="card-head"><h2>Progress updates</h2></div>
            @forelse ($updates as $up)
                <div style="padding:14px 0;border-bottom:1px solid var(--line)">
                    <div class="row between"><div class="row"><x-avatar :user="$up->user"/><div><b>{{ $up->user->name ?? 'Member' }}</b><div class="small muted">{{ $up->created_at->format('M j, Y g:i A') }} · {{ $up->created_at->diffForHumans() }}</div></div></div>
                    @if ($up->task)<a class="badge b-info" href="{{ route('tasks.show', $up->task) }}">{{ \Illuminate\Support\Str::limit($up->task->title, 28) }}</a>@endif</div>
                    <p style="margin:10px 0 0">{{ $up->body }}</p>
                </div>
            @empty
                <div class="empty"><x-icon name="activity"/><div>No updates yet. Members post what they finished or what is blocking them.</div></div>
            @endforelse
        </div>
        @if ($project->members->contains('id', $u->id))
        <form method="POST" action="{{ route('projects.updates.store', $project) }}" class="glass card" data-loading>
            @csrf
            <div class="card-head"><h2>Post an update</h2></div>
            <div class="field"><label for="up_body">What did you do?</label><textarea id="up_body" name="body" placeholder="e.g. Finished the survey questionnaire and sent it to the adviser." required></textarea></div>
            <div class="field"><label for="up_task">Related task (optional)</label>
                <select id="up_task" name="task_id"><option value="">None</option>@foreach ($project->tasks as $t)<option value="{{ $t->id }}">{{ \Illuminate\Support\Str::limit($t->title, 40) }}</option>@endforeach</select>
            </div>
            <button class="btn" type="submit"><x-icon name="send"/> Post update</button>
        </form>
        @endif
    </div>
</div>

{{-- Timeline --}}
<div class="tab-panel" id="tab-timeline" hidden>
    <div class="glass card">
        <div class="card-head"><h2>Project timeline</h2><span class="small muted">Newest first</span></div>
        <ul class="timeline">
            @foreach ($events as $e)
                <li>
                    <span class="dot"><x-icon :name="$e['icon']"/></span>
                    <b>{{ $e['text'] }}</b>
                    <div class="small muted">{{ $e['by'] ?: 'System' }} · {{ $e['at']->format('M j, Y g:i A') }}</div>
                </li>
            @endforeach
        </ul>
    </div>
</div>

{{-- Members --}}
<div class="tab-panel" id="tab-members" hidden>
    <div class="glass card">
        <div class="card-head"><h2>Group members</h2></div>
        <ul class="list">
            @foreach ($project->members as $m)
                <li>
                    <div class="row grow"><x-avatar :user="$m"/><div><b>{{ $m->name }}</b><span class="small muted">{{ $m->email }}@if ($m->student_no) · {{ $m->student_no }}@endif</span></div></div>
                    @if ($m->id === $project->leader_id)
                        <span class="badge b-info">Project Manager</span>
                    @elseif ($isLeader)
                        <form method="POST" action="{{ route('projects.members.remove', [$project, $m]) }}" class="inline-form" data-confirm="Remove {{ $m->name }} from this group?">@csrf @method('DELETE')<button class="btn sm danger">Remove</button></form>
                    @endif
                </li>
            @endforeach
        </ul>
        @if ($isLeader)
            <hr class="divider">
            <form method="POST" action="{{ route('projects.members.add', $project) }}" class="row">
                @csrf
                <div style="flex:1;min-width:220px"><input type="email" name="email" placeholder="Student email to add" required></div>
                <button class="btn">Add member</button>
            </form>
        @endif
    </div>
</div>
@endsection
