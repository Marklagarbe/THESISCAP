@extends('layouts.app')
@section('title', $department->code.' '.ucfirst($kind))
@section('content')
<div class="page-head"><div><div class="crumbs"><a href="{{ route('library.index', $kind) }}">{{ ucfirst($kind) }}</a> / {{ $department->code }}</div><h1>{{ $department->code }} {{ $kind }}</h1><p>{{ $department->name }}</p></div></div>
@forelse ($items as $projectId => $list)
    <div class="glass card">
        <div class="card-head"><h2>{{ optional($projects->get($projectId))->title }}</h2><a href="{{ route('projects.show', $projectId) }}">Open project</a></div>
        <div class="table-wrap"><table>
            <thead><tr><th>Title</th><th>Version</th><th>By</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
            @foreach ($list as $s)
                <tr>
                    <td><a class="title" href="{{ route('submissions.show', $s) }}">{{ $s->title }}</a>@if ($s->chapter_no)<span class="small muted">Chapter {{ $s->chapter_no }}</span>@endif</td>
                    <td>v{{ $s->version }}</td><td class="small">{{ $s->author->name ?? '-' }}</td><td><x-badge :status="$s->status"/></td><td class="small nowrap">{{ $s->created_at->format('M j, Y') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table></div>
    </div>
@empty
    <div class="glass card empty"><x-icon name="book"/><h3>Nothing here yet</h3><p>No {{ $kind }} have been submitted for {{ $department->code }}.</p></div>
@endforelse
@endsection
