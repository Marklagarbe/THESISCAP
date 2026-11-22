@extends('layouts.app')
@section('title', 'Profile')
@section('content')
@php($u = auth()->user())
<div class="page-head"><div><h1>Profile</h1><p>{{ $u->roleLabel() }}</p></div></div>
<div class="grid two">
    <form method="POST" action="{{ route('profile.update') }}" class="glass card">
        @csrf @method('PUT')
        <div class="card-head"><h2>Your details</h2><x-avatar :user="$u" :lg="true"/></div>
        <div class="field"><label for="name">Full name</label><input id="name" type="text" name="name" value="{{ old('name', $u->name) }}" required></div>
        <div class="field"><label for="email">Email</label><input id="email" type="email" name="email" value="{{ old('email', $u->email) }}" required></div>
        <div class="field"><label for="department_id">Department</label>
            <select id="department_id" name="department_id"><option value="">None</option>@foreach ($departments as $d)<option value="{{ $d->id }}" @selected(old('department_id', $u->department_id) == $d->id)>{{ $d->code }}</option>@endforeach</select>
        </div>
        <div class="field"><label for="student_no">ID number (verified at registration)</label><input id="student_no" type="text" value="{{ $u->student_no }}" disabled></div>
        <div class="field"><label for="phone">Phone</label><input id="phone" type="text" name="phone" value="{{ old('phone', $u->phone) }}"></div>
        <button class="btn">Save profile</button>
    </form>
    <form method="POST" action="{{ route('profile.password') }}" class="glass card">
        @csrf @method('PUT')
        <div class="card-head"><h2>Change password</h2></div>
        <div class="field"><label for="current_password">Current password</label><input id="current_password" type="password" name="current_password" required>@error('current_password', 'password')<div class="err">{{ $message }}</div>@enderror</div>
        <div class="field"><label for="new_password">New password</label><input id="new_password" type="password" name="password" required>@error('password', 'password')<div class="err">{{ $message }}</div>@enderror</div>
        <div class="field"><label for="password_confirmation">Confirm new password</label><input id="password_confirmation" type="password" name="password_confirmation" required></div>
        <button class="btn">Change password</button>
    </form>
</div>
@endsection
