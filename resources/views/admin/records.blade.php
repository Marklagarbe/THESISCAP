@extends('layouts.app')
@section('title', 'Records')
@section('content')
<div class="page-head"><div><h1>System records</h1><p>The 25 most recent projects and submissions. Deleting is permanent.</p></div></div>
<div class="glass card">
    <div class="card-head"><h2>Projects</h2></div>
    <div class="table-wrap"><table>
        <thead><tr><th>Project</th><th>Dept</th><th>Leader</th><th>Created</th><th></th></tr></thead>
        <tbody>
        @forelse ($projects as $p)
            <tr><td><a class="title" href="{{ route('projects.show', $p) }}">{{ \Illuminate\Support\Str::limit($p->title, 55) }}</a></td><td>{{ $p->department->code ?? '-' }}</td><td class="small">{{ $p->leader->name ?? '-' }}</td><td class="small nowrap">{{ $p->created_at->format('M j, Y') }}</td>
            <td class="right"><form method="POST" action="{{ route('admin.records.project', $p) }}" class="inline-form" data-confirm="Delete this project with all its tasks, submissions and files?">@csrf @method('DELETE')<button class="btn sm danger"><x-icon name="trash"/> Delete</button></form></td></tr>
        @empty
            <tr><td colspan="5" class="muted">No projects.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div>
<div class="glass card">
    <div class="card-head"><h2>Submissions</h2></div>
    <div class="table-wrap"><table>
        <thead><tr><th>Title</th><th>Project</th><th>Status</th><th>Created</th><th></th></tr></thead>
        <tbody>
        @forelse ($submissions as $s)
            <tr><td><a class="title" href="{{ route('submissions.show', $s) }}">{{ $s->title }}</a><span class="small muted">{{ $s->typeLabel() }}</span></td><td class="small">{{ \Illuminate\Support\Str::limit($s->project->title ?? '', 40) }}</td><td><x-badge :status="$s->status"/></td><td class="small nowrap">{{ $s->created_at->format('M j, Y') }}</td>
            <td class="right"><form method="POST" action="{{ route('admin.records.submission', $s) }}" class="inline-form" data-confirm="Delete this submission and its file?">@csrf @method('DELETE')<button class="btn sm danger"><x-icon name="trash"/> Delete</button></form></td></tr>
        @empty
            <tr><td colspan="5" class="muted">No submissions.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div>
@endsection
