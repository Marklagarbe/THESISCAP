@extends('layouts.app')
@section('title', $submission->title)
@section('content')
@php
    $u = auth()->user();
    $project = $submission->project;
    $canReview = $u->hasPerm('submissions.review') && (in_array($u->id, [$project->instructor_id, $project->adviser_id], true) || $u->role === 'admin');
    $canApprove = $canReview && $u->hasPerm('milestones.approve');
@endphp
<div class="page-head">
    <div>
        <div class="crumbs"><a href="{{ route('submissions.index') }}">Submissions</a> / {{ $submission->typeLabel() }}</div>
        <h1>{{ $submission->title }}</h1>
        <p><x-badge :status="$submission->status"/> &nbsp; Version {{ $submission->version }} · by {{ $submission->author->name ?? 'Unknown' }} · {{ $submission->created_at->format('M j, Y g:i A') }}</p>
    </div>
    @if ($submission->file_path)<a class="btn" href="{{ route('submissions.download', $submission) }}"><x-icon name="download"/> Download file</a>@endif
</div>

<div class="grid side">
    <div>
        <div class="glass card">
            <div class="card-head"><h2>Feedback and revisions</h2></div>
            @forelse ($submission->feedback as $f)
                <div style="padding:14px 0;border-bottom:1px solid var(--line)">
                    <div class="row between"><div class="row"><x-avatar :user="$f->user"/><div><b>{{ $f->user->name ?? 'Staff' }}</b><div class="small muted">{{ $f->user ? $f->user->roleLabel() : '' }} · {{ $f->created_at->diffForHumans() }}</div></div></div>
                    <span class="badge b-{{ $f->kind === 'revision_request' ? 'warn' : ($f->kind === 'approval' ? 'ok' : 'info') }}">{{ $f->kindLabel() }}</span></div>
                    @if ($f->body)<p style="margin:10px 0 0">{{ $f->body }}</p>@endif
                </div>
            @empty
                <div class="empty"><x-icon name="file"/><div>No feedback yet.</div></div>
            @endforelse
        </div>

        @if ($canReview)
        <div class="glass card">
            <div class="card-head"><h2>Your review</h2></div>
            <form method="POST" action="{{ route('submissions.review', $submission) }}" data-loading>
                @csrf
                <div class="field"><label for="body">Feedback</label><textarea id="body" name="body" placeholder="What works, what needs to change?">{{ old('body') }}</textarea></div>
                <div class="row">
                    <button class="btn" name="kind" value="comment">Send feedback</button>
                    <button class="btn ghost" name="kind" value="recommendation">Add recommendation</button>
                    <button class="btn warn" name="kind" value="revision_request">Request revisions</button>
                    <button class="btn ghost" name="kind" value="under_review">Mark under review</button>
                    @if ($canApprove)<button class="btn good" name="kind" value="approval">Approve</button>@endif
                </div>
            </form>
        </div>
        @endif
    </div>
    <div>
        <div class="glass card">
            <div class="card-head"><h2>Version history</h2></div>
            <ul class="list">
                @foreach ($versions as $v)
                    <li>
                        <div class="grow">
                            <a href="{{ route('submissions.show', $v) }}"><b>Version {{ $v->version }}</b></a>@if ($v->id === $submission->id) <span class="badge b-info">Viewing</span>@endif
                            <span class="small muted">{{ $v->author->name ?? 'Unknown' }} · {{ $v->created_at->format('M j, Y') }}</span>
                        </div>
                        <x-badge :status="$v->status"/>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="glass card">
            <div class="card-head"><h2>Details</h2></div>
            <ul class="list">
                <li><span class="muted">Project</span><a href="{{ route('projects.show', $project) }}">{{ \Illuminate\Support\Str::limit($project->title, 34) }}</a></li>
                <li><span class="muted">Department</span><b>{{ $project->department->code ?? '-' }}</b></li>
                <li><span class="muted">Type</span><b>{{ $submission->typeLabel() }}@if ($submission->chapter_no) {{ $submission->chapter_no }}@endif</b></li>
                <li><span class="muted">Milestone</span><b>{{ optional(optional($submission->milestone)->milestone)->title ?? 'None' }}</b></li>
                <li><span class="muted">File</span><b>{{ $submission->original_name ?: 'No file (sample record)' }}</b></li>
                <li><span class="muted">Instructor</span><b>{{ $project->instructor->name ?? 'Not assigned' }}</b></li>
                <li><span class="muted">Adviser</span><b>{{ $project->adviser->name ?? 'Not assigned' }}</b></li>
            </ul>
            @if ($submission->notes)<hr class="divider"><p class="small muted" style="margin:0">Notes: {{ $submission->notes }}</p>@endif
        </div>
    </div>
</div>
@endsection
