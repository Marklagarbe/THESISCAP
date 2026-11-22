@extends('layouts.app')
@section('title', 'Register project')
@section('content')
<div class="page-head"><div><div class="crumbs"><a href="{{ route('projects.index') }}">Projects</a> / New</div><h1>Register your project</h1><p>The standard phases and milestones are added automatically.</p></div></div>
<form method="POST" action="{{ route('projects.store') }}" class="glass card" style="max-width:780px" data-loading>
    @csrf
    <div class="form-grid">
        <div class="field full"><label for="title">Project title</label><input id="title" type="text" name="title" value="{{ old('title') }}" required></div>
        <div class="field"><label for="type">Type</label><select id="type" name="type"><option value="thesis" @selected(old('type') === 'thesis')>Thesis</option><option value="capstone" @selected(old('type') === 'capstone')>Capstone</option></select></div>
        <div class="field"><label for="department_id">Department</label>
            <select id="department_id" name="department_id" required>
                @foreach ($departments as $d)<option value="{{ $d->id }}" @selected(old('department_id', auth()->user()->department_id) == $d->id)>{{ $d->code }}: {{ $d->name }}</option>@endforeach
            </select>
        </div>
        <div class="field full"><label for="due_date">Target completion date (optional)</label><input id="due_date" type="date" name="due_date" value="{{ old('due_date') }}"></div>
        <div class="field full"><label for="abstract">Short description (optional)</label><textarea id="abstract" name="abstract">{{ old('abstract') }}</textarea></div>
    </div>
    <button type="submit" class="btn">Register project</button>
</form>
@endsection
