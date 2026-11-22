@extends('layouts.app')
@section('title', 'New submission')
@section('content')
<div class="page-head"><div><div class="crumbs"><a href="{{ route('submissions.index') }}">Submissions</a> / New</div><h1>Submit a file</h1><p>Your instructor and adviser are notified right away.</p></div></div>
@if ($projects->isEmpty())
    <div class="glass card empty"><x-icon name="folder"/><h3>Join a project first</h3><p>You can submit once you are a member of a project.</p></div>
@else
<form method="POST" action="{{ route('submissions.store') }}" enctype="multipart/form-data" class="glass card" style="max-width:780px" data-loading>
    @csrf
    <div class="form-grid">
        <div class="field full"><label for="project_id">Project</label>
            <select id="project_id" name="project_id">@foreach ($projects as $p)<option value="{{ $p->id }}" @selected(old('project_id') == $p->id)>{{ $p->title }}</option>@endforeach</select>
        </div>
        <div class="field"><label for="type">What are you submitting?</label>
            <select id="type" name="type">@foreach (['requirement' => 'Requirement', 'chapter' => 'Thesis chapter', 'manuscript' => 'Manuscript'] as $v => $l)<option value="{{ $v }}" @selected(old('type') === $v)>{{ $l }}</option>@endforeach</select>
        </div>
        <div class="field"><label for="chapter_no">Chapter number (chapters only)</label><input id="chapter_no" type="number" name="chapter_no" min="1" max="10" value="{{ old('chapter_no') }}"></div>
        <div class="field full"><label for="title">Title</label><input id="title" type="text" name="title" value="{{ old('title') }}" placeholder="e.g. Chapter 2: Review of Related Literature" required>@error('title')<div class="err">{{ $message }}</div>@enderror</div>
        <div class="field full"><label for="project_milestone_id">Related milestone (optional)</label>
            <select id="project_milestone_id" name="project_milestone_id"><option value="">None</option>
                @foreach ($projects->first()->orderedMilestones() as $pm)<option value="{{ $pm->id }}" @selected(old('project_milestone_id') == $pm->id)>{{ $pm->milestone->title }}</option>@endforeach
            </select>
            @if ($projects->count() > 1)<div class="small muted" style="margin-top:6px">Milestones shown are for {{ \Illuminate\Support\Str::limit($projects->first()->title, 40) }}.</div>@endif
        </div>
        <div class="field full"><label for="file">File (PDF, Word, PowerPoint, Excel or ZIP)</label><input id="file" type="file" name="file" required>@error('file')<div class="err">{{ $message }}</div>@enderror</div>
        <div class="field full"><label for="notes">Notes for reviewers (optional)</label><textarea id="notes" name="notes" style="min-height:80px">{{ old('notes') }}</textarea></div>
    </div>
    <button class="btn" type="submit">Submit</button>
</form>
@endif
@endsection
