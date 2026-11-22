@extends('layouts.app')
@section('title', 'Assignments')
@section('content')
<div class="page-head"><div><h1>Instructor and adviser assignments</h1><p>Assign staff to each project. They are notified when you save.</p></div></div>
<div class="glass card">
    <div class="table-wrap"><table>
        <thead><tr><th>Project</th><th>Dept</th><th>Instructor</th><th>Adviser</th><th></th></tr></thead>
        <tbody>
        @forelse ($projects as $p)
            <tr>
                <td><form id="assign-{{ $p->id }}" method="POST" action="{{ route('coordinator.assign', $p) }}">@csrf @method('PUT')</form>
                    <a class="title" href="{{ route('projects.show', $p) }}">{{ \Illuminate\Support\Str::limit($p->title, 42) }}</a><span class="small muted">Leader: {{ $p->leader->name ?? '-' }}</span></td>
                <td>{{ $p->department->code ?? '-' }}</td>
                <td><select name="instructor_id" form="assign-{{ $p->id }}" style="min-width:180px"><option value="">Not assigned</option>@foreach ($instructors as $i)<option value="{{ $i->id }}" @selected($p->instructor_id === $i->id)>{{ $i->name }}</option>@endforeach</select></td>
                <td><select name="adviser_id" form="assign-{{ $p->id }}" style="min-width:180px"><option value="">Not assigned</option>@foreach ($advisers as $a)<option value="{{ $a->id }}" @selected($p->adviser_id === $a->id)>{{ $a->name }}</option>@endforeach</select></td>
                <td><button class="btn sm" form="assign-{{ $p->id }}">Save</button></td>
            </tr>
        @empty
            <tr><td colspan="5" class="muted">No projects registered yet.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div>
@endsection
