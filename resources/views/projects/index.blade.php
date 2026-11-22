@extends('layouts.app')
@section('title', 'Projects')
@section('content')
<div class="page-head">
    <div><h1>Projects</h1><p>{{ auth()->user()->role === 'student' ? 'The projects you belong to.' : 'Thesis and capstone projects you can see.' }}</p></div>
    @if (auth()->user()->hasPerm('projects.create'))<a class="btn" href="{{ route('projects.create') }}"><x-icon name="plus"/> Register project</a>@endif
</div>

<form method="GET" class="glass card no-print">
    <div class="row">
        <div style="flex:2;min-width:200px"><input type="search" name="q" value="{{ request('q') }}" placeholder="Search by title"></div>
        <div style="flex:1;min-width:160px"><select name="department"><option value="">All departments</option>@foreach ($departments as $d)<option value="{{ $d->id }}" @selected(request('department') == $d->id)>{{ $d->code }}</option>@endforeach</select></div>
        <div style="flex:1;min-width:160px"><select name="status"><option value="">Any status</option>@foreach (['ongoing' => 'Ongoing', 'delayed' => 'Delayed', 'completed' => 'Completed'] as $v => $l)<option value="{{ $v }}" @selected(request('status') === $v)>{{ $l }}</option>@endforeach</select></div>
        <button class="btn">Filter</button>
    </div>
</form>

<div class="glass card">
    @include('partials.project-table', ['projects' => $projects, 'emptyText' => 'No projects match. Student Project Managers register new projects.'])
</div>
@endsection
