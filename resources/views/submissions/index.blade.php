@extends('layouts.app')
@section('title', 'Submissions')
@section('content')
@php($u = auth()->user())
<div class="page-head">
    <div><h1>Submissions</h1><p>{{ in_array($u->role, ['instructor', 'adviser']) && ! request()->has('all') && ! request('status') ? 'Waiting for your review.' : 'Requirements, chapters and manuscripts.' }}</p></div>
    @if ($u->hasPerm('submissions.upload'))<a class="btn" href="{{ route('submissions.create') }}"><x-icon name="upload"/> New submission</a>@endif
</div>
<form method="GET" class="glass card no-print">
    <div class="row">
        <div style="flex:1;min-width:160px"><select name="type"><option value="">All types</option>@foreach (['requirement' => 'Requirement', 'chapter' => 'Chapter', 'manuscript' => 'Manuscript'] as $v => $l)<option value="{{ $v }}" @selected(request('type') === $v)>{{ $l }}</option>@endforeach</select></div>
        <div style="flex:1;min-width:160px"><select name="status"><option value="">{{ in_array($u->role, ['instructor', 'adviser']) ? 'To review' : 'Any status' }}</option>@foreach (['submitted' => 'Submitted', 'under_review' => 'Under review', 'revision_requested' => 'Revision requested', 'approved' => 'Approved'] as $v => $l)<option value="{{ $v }}" @selected(request('status') === $v)>{{ $l }}</option>@endforeach</select></div>
        <button class="btn">Filter</button>
        @if (in_array($u->role, ['instructor', 'adviser']))<a class="btn ghost" href="{{ route('submissions.index', ['all' => 1]) }}">Show all</a>@endif
    </div>
</form>
<div class="glass card">
    @include('partials.submission-table', ['submissions' => $submissions])
    <div style="margin-top:14px">{{ $submissions->links() }}</div>
</div>
@endsection
