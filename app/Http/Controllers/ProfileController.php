<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit', ['departments' => Department::orderBy('code')->get()]);
    }

    public function update(Request $request)
    {
        $u = $this->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($u->id)],
            'department_id' => ['nullable', 'exists:departments,id'],
            'phone' => ['nullable', 'string', 'max:40'],
        ]);
        $u->update($data);
        ActivityLog::record('profile.update', 'Updated profile');

        return back()->with('success', 'Profile saved.');
    }

    public function password(Request $request)
    {
        $u = $this->user();
        $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($request->current_password, $u->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.'], 'password');
        }

        $u->update(['password' => $request->password]);
        ActivityLog::record('profile.password', 'Changed password');

        return back()->with('success', 'Password changed.');
    }
}
