@extends('layouts.app')
@section('title', 'Settings')
@section('content')
<div class="page-head"><div><h1>System settings</h1><p>School details, sign-up and upload limits, departments.</p></div></div>
<div class="grid two">
    <form method="POST" action="{{ route('admin.settings.save') }}" class="glass card">
        @csrf @method('PUT')
        <div class="card-head"><h2>General</h2></div>
        <div class="field"><label for="site_name">System name</label><input id="site_name" type="text" name="site_name" value="{{ old('site_name', $values['site_name']) }}" required></div>
        <div class="field"><label for="school_name">School name</label><input id="school_name" type="text" name="school_name" value="{{ old('school_name', $values['school_name']) }}"></div>
        <div class="field"><label for="academic_year">Academic year</label><input id="academic_year" type="text" name="academic_year" value="{{ old('academic_year', $values['academic_year']) }}"></div>
        <div class="field"><label for="max_upload_mb">Maximum upload size (MB)</label><input id="max_upload_mb" type="number" min="1" max="100" name="max_upload_mb" value="{{ old('max_upload_mb', $values['max_upload_mb']) }}" required></div>
        <div class="field"><label class="check"><input type="checkbox" name="allow_registration" value="1" @checked($values['allow_registration'] === '1')> Allow people to create their own accounts</label></div>
        <button class="btn">Save settings</button>
    </form>
    <div class="glass card">
        <div class="card-head"><h2>Departments</h2></div>
        <ul class="list">
            @foreach ($departments as $d)
                <li>
                    <div class="grow"><b>{{ $d->code }}</b><span class="small muted">{{ $d->name }} · {{ $d->users_count }} user(s) · {{ $d->projects_count }} project(s)</span></div>
                    <form method="POST" action="{{ route('admin.departments.destroy', $d) }}" class="inline-form" data-confirm="Delete {{ $d->code }}?">@csrf @method('DELETE')<button class="btn sm danger" aria-label="Delete department"><x-icon name="trash"/></button></form>
                </li>
            @endforeach
        </ul>
        <hr class="divider">
        <form method="POST" action="{{ route('admin.departments.store') }}">
            @csrf
            <div class="form-grid"><div class="field"><label for="d_code">Code</label><input id="d_code" type="text" name="code" placeholder="BSN" required></div><div class="field"><label for="d_name">Name</label><input id="d_name" type="text" name="name" placeholder="BS Nursing" required></div></div>
            <button class="btn sm">Add department</button>
        </form>
    </div>
</div>
@endsection
