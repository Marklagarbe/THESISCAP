@extends('layouts.app')
@section('title', ucfirst($kind))
@section('content')
<div class="page-head"><div><h1>{{ ucfirst($kind) }}</h1><p>Pick a department to see its {{ $kind }}.</p></div></div>
<div class="dept-grid">
    @foreach ($departments as $d)
        <a class="glass dept" href="{{ route('library.department', [$kind, $d->code]) }}">
            <div class="code"><span>{{ $d->code }}</span></div>
            <div class="muted small" style="margin-bottom:14px">{{ $d->name }}</div>
            <div class="row between"><b>{{ $d->item_count }} {{ $kind === 'chapters' ? 'chapter(s)' : 'manuscript(s)' }}</b><span class="small muted">{{ $d->project_count }} project(s)</span></div>
        </a>
    @endforeach
</div>
@endsection
