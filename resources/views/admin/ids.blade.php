@extends('layouts.app')
@section('title', 'Registered IDs')
@section('content')
<div class="page-head"><div><h1>Registered IDs</h1><p>Only people whose ID is in this list can create an account. Each ID works once.</p></div></div>

<div class="stats">
    <div class="glass stat"><div class="lbl">Total IDs</div><div class="num">{{ $total }}</div></div>
    <div class="glass stat good"><div class="lbl">Already registered</div><div class="num">{{ $used }}</div></div>
    <div class="glass stat"><div class="lbl">Still available</div><div class="num">{{ $total - $used }}</div></div>
</div>

<div class="grid two">
    <form method="POST" action="{{ route('admin.ids.store') }}" class="glass card">
        @csrf
        <div class="card-head"><h2>Add one ID</h2></div>
        <div class="form-grid">
            <div class="field"><label for="a_id">ID number</label><input id="a_id" type="text" name="id_number" value="{{ old('id_number') }}" placeholder="TE-3346776" required></div>
            <div class="field"><label for="a_type">Type</label><select id="a_type" name="type">@foreach (\App\Models\RegisteredId::TYPES as $k => $t)<option value="{{ $k }}" @selected(old('type') === $k)>{{ $t[1] }} ({{ $t[0] }}-)</option>@endforeach</select></div>
            <div class="field"><label for="a_name">Name (optional)</label><input id="a_name" type="text" name="full_name" value="{{ old('full_name') }}"></div>
            <div class="field"><label for="a_dept">Department (optional)</label><select id="a_dept" name="department_id"><option value="">None</option>@foreach ($departments as $d)<option value="{{ $d->id }}">{{ $d->code }}</option>@endforeach</select></div>
        </div>
        <button class="btn">Add ID</button>
    </form>
    <form method="POST" action="{{ route('admin.ids.import') }}" class="glass card">
        @csrf
        <div class="card-head"><h2>Add many at once</h2></div>
        <div class="form-grid">
            <div class="field"><label for="i_type">Type</label><select id="i_type" name="type">@foreach (\App\Models\RegisteredId::TYPES as $k => $t)<option value="{{ $k }}">{{ $t[1] }} ({{ $t[0] }}-)</option>@endforeach</select></div>
            <div class="field"><label for="i_dept">Department (optional)</label><select id="i_dept" name="department_id"><option value="">None</option>@foreach ($departments as $d)<option value="{{ $d->id }}">{{ $d->code }}</option>@endforeach</select></div>
        </div>
        <div class="field"><label for="i_lines">One per line: ID or ID, Full name</label><textarea id="i_lines" name="lines" style="min-height:96px" placeholder="ST-1000201, Maria Santos&#10;ST-1000202" required></textarea></div>
        <button class="btn">Import IDs</button>
    </form>
</div>

<form method="GET" class="glass card no-print"><div class="row">
    <div style="flex:2;min-width:200px"><input type="search" name="q" value="{{ request('q') }}" placeholder="Search ID or name"></div>
    <div style="flex:1;min-width:170px"><select name="type"><option value="">All types</option>@foreach (\App\Models\RegisteredId::TYPES as $k => $t)<option value="{{ $k }}" @selected(request('type') === $k)>{{ $t[1] }}</option>@endforeach</select></div>
    <div style="flex:1;min-width:170px"><select name="status"><option value="">Any status</option><option value="available" @selected(request('status') === 'available')>Available</option><option value="used" @selected(request('status') === 'used')>Registered</option></select></div>
    <button class="btn">Filter</button>
</div></form>

<div class="glass card">
    <div class="table-wrap"><table>
        <thead><tr><th>ID number</th><th>Type</th><th>Name</th><th>Dept</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse ($ids as $row)
            <tr>
                <td><b>{{ $row->id_number }}</b></td>
                <td class="small">{{ $row->typeLabel() }}</td>
                <td class="small">{{ $row->full_name ?: '-' }}</td>
                <td>{{ $row->department->code ?? '-' }}</td>
                <td>@if ($row->used_by)<span class="badge b-ok">Registered</span><div class="small muted">{{ $row->user->name ?? '' }}</div>@else<span class="badge b-info">Available</span>@endif</td>
                <td class="right">@unless ($row->used_by)<form method="POST" action="{{ route('admin.ids.destroy', $row) }}" class="inline-form" data-confirm="Delete {{ $row->id_number }}?">@csrf @method('DELETE')<button class="btn sm danger" aria-label="Delete ID"><x-icon name="trash"/></button></form>@endunless</td>
            </tr>
        @empty
            <tr><td colspan="6" class="muted">No IDs match.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    <div style="margin-top:14px">{{ $ids->links() }}</div>
</div>
@endsection
