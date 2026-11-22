@extends('layouts.app')
@section('title', 'Users')
@section('content')
<div class="page-head"><div><h1>User accounts</h1><p>Create, edit, disable or delete accounts.</p></div></div>

<details class="fold" style="margin-bottom:20px"><summary>Create an account</summary><div class="body">
    <form method="POST" action="{{ route('admin.users.store') }}">
        @csrf
        <div class="form-grid">
            <div class="field"><label for="n_name">Full name</label><input id="n_name" type="text" name="name" required></div>
            <div class="field"><label for="n_email">Email</label><input id="n_email" type="email" name="email" required></div>
            <div class="field"><label for="n_role">Role</label><select id="n_role" name="role">@foreach (\App\Models\User::ROLES as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach</select></div>
            <div class="field"><label for="n_dept">Department</label><select id="n_dept" name="department_id"><option value="">None</option>@foreach ($departments as $d)<option value="{{ $d->id }}">{{ $d->code }}</option>@endforeach</select></div>
            <div class="field"><label for="n_sn">Student or employee no.</label><input id="n_sn" type="text" name="student_no"></div>
            <div class="field"><label for="n_pw">Password (min 8)</label><input id="n_pw" type="text" name="password" required></div>
        </div>
        <button class="btn">Create account</button>
    </form>
</div></details>

<form method="GET" class="glass card no-print"><div class="row">
    <div style="flex:2;min-width:200px"><input type="search" name="q" value="{{ request('q') }}" placeholder="Search name or email"></div>
    <div style="flex:1;min-width:180px"><select name="role"><option value="">All roles</option>@foreach (\App\Models\User::ROLES as $v => $l)<option value="{{ $v }}" @selected(request('role') === $v)>{{ $l }}</option>@endforeach</select></div>
    <button class="btn">Filter</button>
</div></form>

<div class="glass card">
    <div class="table-wrap"><table>
        <thead><tr><th>User</th><th>Role</th><th>Dept</th><th>Status</th><th class="right">Actions</th></tr></thead>
        <tbody>
        @foreach ($users as $user)
            <tr>
                <td><div class="row"><x-avatar :user="$user"/><div><b>{{ $user->name }}</b><div class="small muted">{{ $user->email }}</div></div></div></td>
                <td class="small">{{ $user->roleLabel() }}</td>
                <td>{{ $user->department->code ?? '-' }}</td>
                <td>@if ($user->is_active)<span class="badge b-ok">Active</span>@else<span class="badge b-bad">Disabled</span>@endif</td>
                <td class="right nowrap">
                    <form method="POST" action="{{ route('admin.users.toggle', $user) }}" class="inline-form">@csrf @method('PATCH')<button class="btn sm ghost">{{ $user->is_active ? 'Disable' : 'Enable' }}</button></form>
                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline-form" data-confirm="Delete {{ $user->name }}? This cannot be undone.">@csrf @method('DELETE')<button class="btn sm danger" aria-label="Delete user"><x-icon name="trash"/></button></form>
                </td>
            </tr>
            <tr><td colspan="5" style="padding-top:0">
                <details class="fold"><summary>Edit {{ $user->name }}</summary><div class="body">
                    <form method="POST" action="{{ route('admin.users.update', $user) }}">
                        @csrf @method('PUT')
                        <div class="form-grid">
                            <div class="field"><label>Full name</label><input type="text" name="name" value="{{ $user->name }}" required></div>
                            <div class="field"><label>Email</label><input type="email" name="email" value="{{ $user->email }}" required></div>
                            <div class="field"><label>Role</label><select name="role">@foreach (\App\Models\User::ROLES as $v => $l)<option value="{{ $v }}" @selected($user->role === $v)>{{ $l }}</option>@endforeach</select></div>
                            <div class="field"><label>Department</label><select name="department_id"><option value="">None</option>@foreach ($departments as $d)<option value="{{ $d->id }}" @selected($user->department_id === $d->id)>{{ $d->code }}</option>@endforeach</select></div>
                            <div class="field"><label>Student or employee no.</label><input type="text" name="student_no" value="{{ $user->student_no }}"></div>
                            <div class="field"><label>New password (leave blank to keep)</label><input type="text" name="password" placeholder="Unchanged"></div>
                        </div>
                        <button class="btn sm">Save changes</button>
                    </form>
                </div></details>
            </td></tr>
        @endforeach
        </tbody>
    </table></div>
    <div style="margin-top:14px">{{ $users->links() }}</div>
</div>
@endsection
