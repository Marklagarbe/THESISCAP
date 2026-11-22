@props(['status'])
@php
    $map = [
        'pending' => ['muted', 'Pending'], 'in_progress' => ['info', 'In progress'], 'submitted' => ['info', 'Submitted'],
        'revision' => ['warn', 'Needs revision'], 'approved' => ['ok', 'Approved'], 'todo' => ['muted', 'To do'],
        'done' => ['ok', 'Done'], 'under_review' => ['info', 'Under review'], 'revision_requested' => ['warn', 'Revision requested'],
        'ongoing' => ['info', 'Ongoing'], 'completed' => ['ok', 'Completed'], 'delayed' => ['bad', 'Delayed'],
        'thesis' => ['info', 'Thesis'], 'capstone' => ['info', 'Capstone'],
    ];
    [$cls, $label] = $map[$status] ?? ['muted', ucfirst(str_replace('_', ' ', $status))];
@endphp
<span class="badge b-{{ $cls }}">{{ $label }}</span>
