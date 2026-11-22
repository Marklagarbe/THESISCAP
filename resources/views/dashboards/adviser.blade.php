@extends('layouts.app')
@section('title', 'Adviser Dashboard')
@section('content')
<div class="page-head"><div><h1>Adviser dashboard</h1><p>Review chapters, send recommendations and approve milestones.</p></div></div>
<div class="stats">
    <div class="glass stat"><div class="lbl">Advised projects</div><div class="num">{{ $projects->count() }}</div></div>
    <div class="glass stat {{ $chapters->count() ? 'hot' : '' }}"><div class="lbl">Chapters to review</div><div class="num">{{ $chapters->count() }}</div></div>
    <div class="glass stat {{ $awaiting->count() ? 'hot' : '' }}"><div class="lbl">Milestones awaiting approval</div><div class="num">{{ $awaiting->count() }}</div></div>
    <div class="glass stat"><div class="lbl">Open revisions</div><div class="num">{{ $revisions->count() }}</div></div>
</div>
<div class="grid two">
    <div class="glass card">
        <div class="card-head"><h2>Awaiting your approval</h2></div>
        <ul class="list">
            @forelse ($awaiting as $pm)
                <li><div class="grow"><b>{{ $pm->milestone->title }}</b><span class="small muted">{{ \Illuminate\Support\Str::limit($pm->project->title, 50) }}</span></div><a class="btn sm" href="{{ route('projects.show', $pm->project) }}">Review</a></li>
            @empty
                <li class="muted">Nothing is waiting for approval.</li>
            @endforelse
        </ul>
    </div>
    <div class="glass card">
        <div class="card-head"><h2>Chapters to review</h2></div>
        @include('partials.submission-table', ['submissions' => $chapters])
    </div>
</div>
<div class="glass card">
    <div class="card-head"><h2>Revisions in progress</h2></div>
    @include('partials.submission-table', ['submissions' => $revisions])
</div>
<div class="glass card">
    <div class="card-head"><h2>Advised projects</h2></div>
    @include('partials.project-table', ['projects' => $projects, 'emptyText' => 'No projects assigned to you yet.'])
</div>
@endsection
