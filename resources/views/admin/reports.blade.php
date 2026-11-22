@extends('layouts.app')
@section('title', 'System Reports')
@section('content')
<div class="page-head"><div><h1>System-wide reports</h1><p>Usage and progress across all departments.</p></div><button class="btn ghost no-print" onclick="window.print()"><x-icon name="file"/> Print</button></div>
<div class="stats">
    <div class="glass stat"><div class="lbl">Ongoing projects</div><div class="num">{{ $projectStatus['ongoing'] }}</div></div>
    <div class="glass stat {{ $projectStatus['delayed'] ? 'hot' : '' }}"><div class="lbl">Delayed projects</div><div class="num">{{ $projectStatus['delayed'] }}</div></div>
    <div class="glass stat good"><div class="lbl">Completed projects</div><div class="num">{{ $projectStatus['completed'] }}</div></div>
    <div class="glass stat"><div class="lbl">Sign-ins, last 7 days</div><div class="num">{{ $logins }}</div></div>
</div>
<div class="grid two">
    <div class="glass card">
        <div class="card-head"><h2>By department</h2></div>
        <div class="table-wrap"><table><thead><tr><th>Dept</th><th>Users</th><th>Projects</th><th>Submissions</th></tr></thead><tbody>
            @foreach ($byDept as $r)<tr><td><b>{{ $r['code'] }}</b></td><td>{{ $r['users'] }}</td><td>{{ $r['projects'] }}</td><td>{{ $r['submissions'] }}</td></tr>@endforeach
        </tbody></table></div>
    </div>
    <div>
        <div class="glass card"><div class="card-head"><h2>Accounts by role</h2></div><ul class="list">@foreach ($byRole as $r)<li><span>{{ $r['label'] }}</span><b>{{ $r['count'] }}</b></li>@endforeach</ul></div>
        <div class="glass card"><div class="card-head"><h2>Submissions by status</h2></div><ul class="list">@forelse ($bySubmission as $status => $total)<li><x-badge :status="$status"/><b>{{ $total }}</b></li>@empty<li class="muted">No submissions yet.</li>@endforelse</ul></div>
    </div>
</div>
@endsection
