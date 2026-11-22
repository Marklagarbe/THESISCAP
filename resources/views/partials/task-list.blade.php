@php($u = auth()->user())
@if ($tasks->isEmpty())
    <div class="empty"><x-icon name="check"/><div>{{ $emptyText ?? 'No tasks yet.' }}</div></div>
@else
<ul class="list">
    @foreach ($tasks as $t)
        <li>
            <div class="grow">
                <a href="{{ route('tasks.show', $t) }}"><b>{{ $t->title }}</b></a>
                <span class="small muted">{{ $t->project->title ?? '' }}@if ($t->assignee) · {{ $t->assignee->name }}@endif @if ($t->due_date) · Due {{ $t->due_date->format('M j') }}@endif</span>
                @if ($t->isOverdue())<span class="badge b-bad" style="margin-top:4px">Overdue</span>@endif
            </div>
            @if ($u->hasPerm('tasks.update') && ($u->id === $t->assigned_to || $u->id === optional($t->project)->leader_id))
                <form method="POST" action="{{ route('tasks.update', $t) }}" class="inline-form">
                    @csrf @method('PATCH')
                    <select name="status" onchange="this.form.submit()" style="min-height:38px;padding:6px 10px;width:auto">
                        @foreach (['todo' => 'To do', 'in_progress' => 'In progress', 'done' => 'Done'] as $v => $l)
                            <option value="{{ $v }}" @selected($t->status === $v)>{{ $l }}</option>
                        @endforeach
                    </select>
                </form>
            @else
                <x-badge :status="$t->status"/>
            @endif
        </li>
    @endforeach
</ul>
@endif
