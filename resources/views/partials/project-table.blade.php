@if ($projects->isEmpty())
    <div class="empty"><x-icon name="folder"/><div>{{ $emptyText ?? 'No projects to show.' }}</div></div>
@else
<div class="table-wrap"><table>
    <thead><tr><th>Project</th><th>Dept</th><th>Leader</th><th style="width:20%">Progress</th><th>Status</th></tr></thead>
    <tbody>
    @foreach ($projects as $p)
        <tr>
            <td><a class="title" href="{{ route('projects.show', $p) }}">{{ \Illuminate\Support\Str::limit($p->title, 55) }}</a><span class="small muted">{{ ucfirst($p->type) }}</span></td>
            <td>{{ $p->department->code ?? '-' }}</td>
            <td class="small">{{ $p->leader->name ?? '-' }}</td>
            <td><x-progress :value="$p->progress()"/></td>
            <td><x-badge :status="$p->statusLabel()"/></td>
        </tr>
    @endforeach
    </tbody>
</table></div>
@endif
