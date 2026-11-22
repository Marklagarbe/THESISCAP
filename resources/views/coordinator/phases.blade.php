@extends('layouts.app')
@section('title', 'Phases & Milestones')
@section('content')
<div class="page-head"><div><h1>Phases and milestones</h1><p>Changes apply to every active project. Due dates here become each group's deadlines.</p></div></div>

<div class="grid two">
    <form method="POST" action="{{ route('coordinator.phases.store') }}" class="glass card">
        @csrf
        <div class="card-head"><h2>Add a phase</h2></div>
        <div class="field"><label for="name">Phase name</label><input id="name" type="text" name="name" required></div>
        <div class="field"><label for="description">Description</label><input id="description" type="text" name="description"></div>
        <button class="btn">Add phase</button>
    </form>
    <form method="POST" action="{{ route('coordinator.milestones.store') }}" class="glass card">
        @csrf
        <div class="card-head"><h2>Add a milestone</h2></div>
        <div class="field"><label for="phase_id">Phase</label><select id="phase_id" name="phase_id">@foreach ($phases as $ph)<option value="{{ $ph->id }}">{{ $ph->name }}</option>@endforeach</select></div>
        <div class="form-grid">
            <div class="field"><label for="m_title">Milestone</label><input id="m_title" type="text" name="title" required></div>
            <div class="field"><label for="m_due">Due date</label><input id="m_due" type="date" name="due_date"></div>
        </div>
        <button class="btn">Add milestone</button>
    </form>
</div>

@foreach ($phases as $phase)
<div class="glass card">
    <div class="card-head">
        <div><h2>{{ $phase->name }}</h2><div class="small muted">{{ $phase->description }}</div></div>
        <form method="POST" action="{{ route('coordinator.phases.destroy', $phase) }}" data-confirm="Delete this phase and all its milestones from every project?">@csrf @method('DELETE')<button class="btn sm danger"><x-icon name="trash"/> Delete phase</button></form>
    </div>
    @forelse ($phase->milestones as $m)
        <div class="row between" style="padding:10px 0;border-top:1px solid var(--line)">
            <form method="POST" action="{{ route('coordinator.milestones.update', $m) }}" class="row" style="flex:1">
                @csrf @method('PUT')
                <input type="text" name="title" value="{{ $m->title }}" style="flex:2;min-width:200px;min-height:42px">
                <input type="date" name="due_date" value="{{ optional($m->due_date)->format('Y-m-d') }}" style="flex:1;min-width:150px;min-height:42px">
                <button class="btn sm ghost">Save</button>
            </form>
            <form method="POST" action="{{ route('coordinator.milestones.destroy', $m) }}" data-confirm="Delete this milestone from every project?">@csrf @method('DELETE')<button class="btn sm danger" aria-label="Delete milestone"><x-icon name="trash"/></button></form>
        </div>
    @empty
        <div class="muted small">No milestones in this phase yet.</div>
    @endforelse
</div>
@endforeach
@endsection
