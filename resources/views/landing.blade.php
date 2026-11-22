@extends('layouts.guest')
@section('title', 'Thesis and Capstone Milestone Monitoring')
@section('content')
<div class="tiles" aria-hidden="true"></div>
<div class="wrap">
    <header class="nav-top">
        <a href="{{ route('home') }}" class="brand"><x-icon name="cap"/> ThesisCap</a>
        <div class="row">
            @auth
                <a class="btn" href="{{ route('dashboard') }}">Open dashboard</a>
            @else
                <a class="btn ghost sm" href="{{ route('login') }}">Sign in</a>
                <a class="btn sm" href="{{ route('register') }}">Create account</a>
            @endauth
        </div>
    </header>

    <section class="grid two" style="align-items:center;padding-top:40px">
        <div class="hero">
            <h1>Build Your Future, <span class="grad">One Milestone</span> at a Time</h1>
            <p class="lead">ThesisCap is one workspace for students, instructors, advisers and coordinators to follow every thesis and capstone from proposal to final defense.</p>
            <div class="row"><a class="btn" href="{{ route('register') }}">Get started</a><a class="btn ghost" href="{{ route('login') }}">Sign in</a></div>
        </div>
        <div>
            <a class="glass portal" href="{{ route('login') }}">
                <div class="row"><span class="avatar"><x-icon name="user"/></span><h3 style="margin:0">Student Member</h3></div>
                <p class="muted" style="margin:10px 0 0">See your milestones and tasks, submit requirements and read feedback from your adviser.</p>
            </a>
            <a class="glass portal" href="{{ route('login') }}">
                <div class="row"><span class="avatar"><x-icon name="flag"/></span><h3 style="margin:0">Student Project Manager</h3></div>
                <p class="muted" style="margin:10px 0 0">Run your group: assign tasks, watch member progress and keep every deadline in view.</p>
            </a>
            <a class="glass portal" href="{{ route('login') }}">
                <div class="row"><span class="avatar"><x-icon name="shield"/></span><h3 style="margin:0">Instructors, advisers and staff</h3></div>
                <p class="muted" style="margin:10px 0 0">Review submissions, approve milestones and monitor every project in your department.</p>
            </a>
        </div>
    </section>

    <section class="section">
        <h2>Everyone gets the view they need</h2>
        <p class="lead">Six roles, each with its own landing page and tools.</p>
        <div class="grid three">
            @foreach ([
                ['user', 'Student Member', ['See project milestones and assigned tasks', 'Submit requirements', 'Read feedback and revisions', 'Track personal progress']],
                ['flag', 'Student Project Manager', ['Manage group members', 'Assign group tasks', 'Monitor member progress', 'Track group deadlines']],
                ['users', 'Instructor', ['View assigned groups', 'Review submissions', 'Give feedback', 'Request revisions']],
                ['book', 'Adviser', ['Review thesis chapters', 'Give recommendations', 'Monitor revisions', 'Approve milestones']],
                ['layers', 'Project Coordinator', ['See all registered projects', 'Manage phases and milestones', 'Track delayed projects', 'Generate accomplishment reports']],
                ['shield', 'Administrator', ['Manage accounts, roles and permissions', 'Configure system settings', 'View system-wide reports', 'Read activity logs']],
            ] as [$icon, $name, $items])
                <div class="glass role-card">
                    <div class="ico"><x-icon :name="$icon"/></div>
                    <h3>{{ $name }}</h3>
                    <ul>@foreach ($items as $it)<li>{{ $it }}</li>@endforeach</ul>
                </div>
            @endforeach
        </div>
    </section>

    <section class="section">
        <h2>From proposal to hardbound copy</h2>
        <p class="lead">Every project follows the same phases, so nothing slips between the cracks.</p>
        <div class="steps">
            @foreach ([['Proposal', 'Topic approval and title defense'], ['Chapters 1 to 3', 'Introduction, literature and methodology'], ['Pre-oral defense', 'Panel review of the proposal'], ['Development and results', 'Build, gather data and write Chapters 4 and 5'], ['Final defense', 'Final manuscript and hardbound submission']] as [$t, $d])
                <div class="glass step"><h3>{{ $t }}</h3><p class="muted small" style="margin:0">{{ $d }}</p></div>
            @endforeach
        </div>
    </section>

    <section class="section">
        <h2>Chapters and manuscripts, by department</h2>
        <p class="lead">Browse every chapter and manuscript under its own department: BSIT, BSHM, BSBA and more.</p>
        <div class="row">@foreach (['BSIT', 'BSHM', 'BSBA', 'BSED', 'BSCRIM'] as $c)<span class="badge b-info" style="font-size:.95rem;padding:8px 18px">{{ $c }}</span>@endforeach</div>
    </section>

    <footer class="footer">ThesisCap · Thesis and Capstone Milestone Monitoring System</footer>
</div>
@endsection
