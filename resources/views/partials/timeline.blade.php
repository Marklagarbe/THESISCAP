@php
    $u = auth()->user();
    $actions = $actions ?? false;
    $isLeader = $u->id === $project->leader_id;
    $canApprove = $u->hasPerm('milestones.approve') && ($u->id === $project->adviser_id || $u->hasPerm('projects.view_all'));
    $canRevise = $u->hasPerm('submissions.review') && (in_array($u->id, [$project->instructor_id, $project->adviser_id], true) || $u->role === 'admin');
    $lastPhase = null;
@endphp
<ul class="timeline">
@foreach ($project->orderedMilestones() as $pm)
    @php
        $late = $pm->isDelayed();
        $cls = $pm->status === 'approved' ? 'done' : ($late ? 'late' : (in_array($pm->status, ['in_progress', 'submitted', 'revision'], true) ? 'live' : ''));
    @endphp
    <li class="{{ $cls }}">
        <span class="dot">@if ($pm->status === 'approved')<x-icon name="tick"/>@endif</span>
        <div class="row between" style="align-items:flex-start">
            <div>
                <b>{{ $pm->milestone->title }}</b>
                <div class="small muted">{{ $pm->milestone->phase->name }} · Due {{ $pm->due_date ? $pm->due_date->format('M j, Y') : 'no date set' }}@if ($late) · <span style="color:#ffb1b1">{{ $pm->daysLate() }} days late</span>@endif</div>
                @if ($pm->status === 'revision' && $pm->remarks)<div class="small" style="color:#fcd980">Note: {{ $pm->remarks }}</div>@endif
            </div>
            <x-badge :status="$pm->status"/>
        </div>
        @if ($actions && $pm->status !== 'approved')
            <div class="row no-print" style="margin-top:10px">
                @if ($isLeader && in_array($pm->status, ['pending', 'revision'], true))
                    <form method="POST" action="{{ route('milestones.update', $pm) }}" class="inline-form">@csrf @method('PATCH')<input type="hidden" name="action" value="start"><button class="btn sm ghost">Start working</button></form>
                @endif
                @if ($isLeader && in_array($pm->status, ['pending', 'in_progress', 'revision'], true))
                    <form method="POST" action="{{ route('milestones.update', $pm) }}" class="inline-form">@csrf @method('PATCH')<input type="hidden" name="action" value="submit"><button class="btn sm">Submit for approval</button></form>
                @endif
                @if ($canApprove && $pm->status === 'submitted')
                    <form method="POST" action="{{ route('milestones.update', $pm) }}" class="inline-form">@csrf @method('PATCH')<input type="hidden" name="action" value="approve"><button class="btn sm good">Approve</button></form>
                @endif
                @if ($canRevise && $pm->status !== 'pending')
                    <form method="POST" action="{{ route('milestones.update', $pm) }}" class="row" style="gap:6px">@csrf @method('PATCH')<input type="hidden" name="action" value="revise"><input type="text" name="remarks" placeholder="What needs to change?" style="min-height:36px;width:200px;padding:6px 12px"><button class="btn sm warn">Request revision</button></form>
                @endif
            </div>
        @endif
    </li>
@endforeach
</ul>
