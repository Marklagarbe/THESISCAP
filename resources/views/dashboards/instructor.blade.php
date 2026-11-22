@extends('layouts.app')
@section('title', 'Instructor Dashboard')
@section('content')
<div class="page-head"><div><h1>Instructor dashboard</h1><p>Monitor your groups and review what they submit.</p></div><a class="btn" href="{{ route('submissions.index') }}"><x-icon name="upload"/> Review queue</a></div>
<div class="stats">
    <div class="glass stat"><div class="lbl">Assigned groups</div><div class="num">{{ $projects->count() }}</div></div>
    <div class="glass stat {{ $pending->count() ? 'hot' : '' }}"><div class="lbl">Waiting for review</div><div class="num">{{ $pending->count() }}</div></div>
    <div class="glass stat {{ $projects->filter->isDelayed()->count() ? 'hot' : '' }}"><div class="lbl">Delayed groups</div><div class="num">{{ $projects->filter->isDelayed()->count() }}</div></div>
    <div class="glass stat good"><div class="lbl">Average progress</div><div class="num">{{ $projects->count() ? (int) round($projects->avg(fn ($p) => $p->progress())) : 0 }}%</div></div>
</div>
<div class="glass card">
    <div class="card-head"><h2>Submissions to review</h2></div>
    @include('partials.submission-table', ['submissions' => $pending])
</div>
<div class="glass card">
    <div class="card-head"><h2>Student progress by group</h2></div>
    @if ($projects->isEmpty())
        <div class="empty"><x-icon name="users"/><div>No groups assigned yet. The Project Coordinator assigns instructors.</div></div>
    @else
        <div class="table-wrap"><table>
            <thead><tr><th>Group</th><th>Members</th><th style="width:25%">Progress</th><th>Current milestone</th><th>Status</th></tr></thead>
            <tbody>
            @foreach ($projects as $p)
                <tr>
                    <td><a class="title" href="{{ route('projects.show', $p) }}">{{ \Illuminate\Support\Str::limit($p->title, 45) }}</a><span class="small muted">Leader: {{ $p->leader->name ?? '-' }}</span></td>
                    <td>{{ $p->members->count() }}</td>
                    <td><x-progress :value="$p->progress()"/></td>
                    <td class="small">{{ optional(optional($p->currentMilestone())->milestone)->title ?? 'All done' }}</td>
                    <td><x-badge :status="$p->statusLabel()"/></td>
                </tr>
            @endforeach
            </tbody>
        </table></div>
    @endif
</div>
@endsection
