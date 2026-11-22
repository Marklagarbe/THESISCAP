@extends('layouts.guest')
@section('title', 'Create account')
@section('content')
<div class="stage">
    @include('partials.hero')

    <div class="glass auth-card" style="max-width:580px">
        <h2>Create your account</h2>
        <p class="sub">Join your group's workspace. It takes a minute.</p>

        <form method="POST" action="{{ route('register.store') }}" data-loading novalidate>
            @csrf
            <div class="form-grid">
                <div class="field full">
                    <label for="id_number">ID number</label>
                    <div class="input"><x-icon name="shield"/><input id="id_number" type="text" name="id_number" value="{{ old('id_number') }}" placeholder="e.g. ST-1000101 or TE-3346776" autocomplete="off" style="text-transform:uppercase" required></div>
                    <div class="small muted" style="margin-top:6px">Use the ID your school gave you. It must already be in the system.</div>
                    @error('id_number')<div class="err">{{ $message }}</div>@enderror
                </div>
                <div class="field full">
                    <label for="name">Full name</label>
                    <div class="input"><x-icon name="user"/><input id="name" type="text" name="name" value="{{ old('name') }}" required></div>
                    @error('name')<div class="err">{{ $message }}</div>@enderror
                </div>
                <div class="field full">
                    <label for="email">Email address</label>
                    <div class="input"><x-icon name="mail"/><input id="email" type="email" name="email" value="{{ old('email') }}" required></div>
                    @error('email')<div class="err">{{ $message }}</div>@enderror
                </div>
                <div class="field">
                    <label for="role">I am a</label>
                    <select id="role" name="role" required>
                        @foreach (['student' => 'Student Member', 'leader' => 'Student Project Manager', 'instructor' => 'Instructor', 'adviser' => 'Adviser', 'coordinator' => 'Project Coordinator'] as $v => $l)
                            <option value="{{ $v }}" @selected(old('role', 'student') === $v)>{{ $l }}</option>
                        @endforeach
                    </select>
                    @error('role')<div class="err">{{ $message }}</div>@enderror
                </div>
                <div class="field">
                    <label for="department_id">Department <span class="faint">(skipped if your ID has one)</span></label>
                    <select id="department_id" name="department_id">
                        <option value="">Choose department</option>
                        @foreach ($departments as $d)
                            <option value="{{ $d->id }}" @selected(old('department_id') == $d->id)>{{ $d->code }}</option>
                        @endforeach
                    </select>
                    @error('department_id')<div class="err">{{ $message }}</div>@enderror
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <div class="input"><x-icon name="lock"/><input id="password" type="password" name="password" required>@include('partials.pw-toggle', ['target' => 'password'])</div>
                    @error('password')<div class="err">{{ $message }}</div>@enderror
                </div>
                <div class="field">
                    <label for="password_confirmation">Confirm password</label>
                    <div class="input"><x-icon name="lock"/><input id="password_confirmation" type="password" name="password_confirmation" required>@include('partials.pw-toggle', ['target' => 'password_confirmation'])</div>
                </div>
            </div>
            <button type="submit" class="btn block">Create account</button>
        </form>
        @if (isset($sampleIds) && $sampleIds->isNotEmpty())
            <details class="fold" style="margin-bottom:14px">
                <summary>Sample IDs for testing</summary>
                <div class="body">
                    <p class="small muted" style="margin-bottom:4px">Click an ID to fill the field. Each ID works once.</p>
                    @foreach ($sampleIds as $sid)
                        <button type="button" class="demo-btn" data-fill-id="{{ $sid->id_number }}"><span>{{ $sid->id_number }}</span><small>{{ $sid->typeLabel() }}{{ $sid->full_name ? ' · '.$sid->full_name : '' }}</small></button>
                    @endforeach
                </div>
            </details>
        @endif
        <p class="auth-foot">Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
    </div>
</div>
@endsection
