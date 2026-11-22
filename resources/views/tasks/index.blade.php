@extends('layouts.app')
@section('title', 'Tasks')
@section('content')
@php($u = auth()->user())
<div class="page-head"><div><h1>{{ $u->role === 'leader' ? 'Group tasks' : ($u->role === 'student' ? 'My tasks' : 'Tasks') }}</h1><p>{{ $u->role === 'leader' ? 'Assign work to your members and follow it to done.' : 'Update the status as you work.' }}</p></div></div>

@if ($u->role === 'leader' && $ledProjects->isNotEmpty())
<div class="glass card">
    <div class="card-head"><h2>Assign a task</h2></div>
    <form method="POST" action="{{ route('tasks.store') }}" data-loading>
        @csrf
        <div class="form-grid">
            <div class="field"><label for="project_id">Project</label>
                <select id="project_id" name="project_id">@foreach ($ledProjects as $p)<option value="{{ $p->id }}">{{ \Illuminate\Support\Str::limit($p->title, 50) }}</option>@endforeach</select>
            </div>
            <div class="field"><label for="assigned_to">Assign to</label>
                <select id="assigned_to" name="assigned_to">@foreach ($ledProjects->first()->members as $m)<option value="{{ $m->id }}">{{ $m->name }}</option>@endforeach</select>
            </div>
            <div class="field full"><label for="title">Task</label><input id="title" type="text" name="title" value="{{ old('title') }}" required></div>
            <div class="field"><label for="due_date">Due date</label><input id="due_date" type="date" name="due_date" value="{{ old('due_date') }}"></div>
            <div class="field"><label for="project_milestone_id">Linked milestone (optional)</label>
                <select id="project_milestone_id" name="project_milestone_id"><option value="">None</option>
                    @foreach ($ledProjects->first()->orderedMilestones() as $pm)<option value="{{ $pm->id }}">{{ $pm->milestone->title }}</option>@endforeach
                </select>
            </div>
            <div class="field full"><label for="description">Details (optional)</label><textarea id="description" name="description" style="min-height:80px">{{ old('description') }}</textarea></div>
        </div>
        <button class="btn" type="submit">Assign task</button>
        @if ($ledProjects->count() > 1)<span class="small muted" style="margin-left:10px">Members and milestones shown are for {{ \Illuminate\Support\Str::limit($ledProjects->first()->title, 30) }}.</span>@endif
    </form>
</div>
@endif

<div class="glass card">
    <div class="card-head">
        <h2>{{ $tasks->count() }} task(s)</h2>
        <form method="GET" class="row no-print"><select name="status" onchange="this.form.submit()" style="width:auto;min-height:40px"><option value="">All statuses</option>@foreach (['todo' => 'To do', 'in_progress' => 'In progress', 'done' => 'Done'] as $v => $l)<option value="{{ $v }}" @selected(request('status') === $v)>{{ $l }}</option>@endforeach</select></form>
    </div>
    @include('partials.task-list', ['tasks' => $tasks, 'emptyText' => 'Nothing here.'])
    @if ($u->role === 'leader' && $tasks->isNotEmpty())
        <hr class="divider">
        <details class="fold"><summary>Delete a task</summary><div class="body">
            @foreach ($tasks as $t)
                <form method="POST" action="{{ route('tasks.destroy', $t) }}" data-confirm="Delete this task?" class="row between" style="padding:8px 0">@csrf @method('DELETE')<span class="small">{{ $t->title }}</span><button class="btn sm danger">Delete</button></form>
            @endforeach
        </div></details>
    @endif
</div>
@endsection
