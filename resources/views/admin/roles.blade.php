@extends('layouts.app')
@section('title', 'Roles & Permissions')
@section('content')
<div class="page-head"><div><h1>Roles and permissions</h1><p>Choose what each role can do. Administrators always have full access.</p></div></div>
<form method="POST" action="{{ route('admin.roles.save') }}" class="glass card">
    @csrf @method('PUT')
    <div class="table-wrap"><table>
        <thead><tr><th>Permission</th>@foreach ($roles as $label)<th class="right">{{ $label }}</th>@endforeach</tr></thead>
        <tbody>
        @foreach ($permissions as $key => $label)
            <tr>
                <td>{{ $label }}<div class="small faint">{{ $key }}</div></td>
                @foreach ($roles as $role => $roleLabel)
                    <td class="right"><input type="checkbox" name="perm[{{ $role }}][]" value="{{ $key }}" aria-label="{{ $roleLabel }}: {{ $label }}" @checked(in_array($key, $granted[$role] ?? [], true))></td>
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table></div>
    <div style="margin-top:18px"><button class="btn">Save permissions</button></div>
</form>
@endsection
