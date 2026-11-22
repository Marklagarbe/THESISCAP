@if ($submissions->isEmpty())
    <div class="empty"><x-icon name="upload"/><div>No submissions yet.</div></div>
@else
<div class="table-wrap"><table>
    <thead><tr><th>Title</th><th>Project</th><th>By</th><th>Status</th><th>Date</th></tr></thead>
    <tbody>
    @foreach ($submissions as $s)
        <tr>
            <td><a class="title" href="{{ route('submissions.show', $s) }}">{{ $s->title }}</a><span class="small muted">{{ $s->typeLabel() }}@if ($s->chapter_no) {{ $s->chapter_no }}@endif · v{{ $s->version }}</span></td>
            <td class="small">{{ \Illuminate\Support\Str::limit($s->project->title ?? '', 40) }}</td>
            <td class="small">{{ $s->author->name ?? 'Unknown' }}</td>
            <td><x-badge :status="$s->status"/></td>
            <td class="small nowrap">{{ $s->created_at->format('M j, Y') }}</td>
        </tr>
    @endforeach
    </tbody>
</table></div>
@endif
