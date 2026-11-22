@extends('layouts.app')
@section('title', 'Delayed Projects')
@section('content')
<div class="page-head"><div><h1>Delayed projects</h1><p>Projects with milestones past their due date.</p></div></div>
@forelse ($projects as $p)
    <div class="glass card">
        <div class="card-head">
            <div><h2><a href="{{ route('projects.show', $p) }}" style="color:#fff">{{ $p->title }}</a></h2><div class="small muted">{{ $p->department->code ?? '' }} · Leader: {{ $p->leader->name ?? '-' }} · Instructor: {{ $p->instructor->name ?? 'none' }} · Adviser: {{ $p->adviser->name ?? 'none' }}</div></div>
            <x-badge status="delayed"/>
        </div>
        <x-progress :value="$p->progress()"/>
        <ul class="list" style="margin-top:12px">
            @foreach ($p->delayedMilestones() as $pm)
                <li><div class="grow"><b>{{ $pm->milestone->title }}</b><span class="small muted">Due {{ $pm->due_date->format('M j, Y') }}</span></div><span class="badge b-bad">{{ $pm->daysLate() }} days late</span></li>
            @endforeach
        </ul>
    </div>
@empty
    <div class="glass card empty"><x-icon name="tick"/><h3>No delayed projects</h3><p>Every project is on schedule.</p></div>
@endforelse
@endsection
