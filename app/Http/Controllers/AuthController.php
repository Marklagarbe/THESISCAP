<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\RegisteredId;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $cred = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($cred, $request->boolean('remember'))) {
            if (! Auth::user()->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors(['email' => 'This account is disabled. Contact the administrator.'])->onlyInput('email');
            }

            $request->session()->regenerate();
            ActivityLog::record('login', 'Signed in', Auth::id());

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors(['email' => 'Email or password is incorrect.'])->onlyInput('email');
    }

    public function showRegister()
    {
        abort_unless(Setting::get('allow_registration', '1') === '1', 403, 'Registration is closed. Ask the administrator for an account.');

        return view('auth.register', [
            'departments' => Department::orderBy('code')->get(),
            'sampleIds' => config('thesiscap.show_demo') ? RegisteredId::whereNull('used_by')->orderBy('id_number')->take(8)->get() : collect(),
        ]);
    }

    public function register(Request $request)
    {
        abort_unless(Setting::get('allow_registration', '1') === '1', 403, 'Registration is closed.');

        $data = $request->validate([
            'id_number' => ['required', 'string', 'max:30'],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'role' => ['required', 'in:student,leader,instructor,adviser,coordinator'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // The ID must already exist in the registry and not be used yet.
        $idNumber = RegisteredId::normalize($data['id_number']);
        $registered = RegisteredId::where('id_number', $idNumber)->first();

        if (! $registered) {
            return back()->withErrors(['id_number' => 'This ID is not in the system. Check the number or ask your administrator to add it.'])->withInput();
        }
        if ($registered->used_by) {
            return back()->withErrors(['id_number' => 'This ID is already registered to an account.'])->withInput();
        }
        if (! in_array($data['role'], $registered->allowedRoles(), true)) {
            return back()->withErrors(['role' => 'This is a '.strtolower($registered->typeLabel()).' ID, so you cannot register as '.(User::ROLES[$data['role']] ?? $data['role']).'.'])->withInput();
        }

        $departmentId = $registered->department_id ?: ($data['department_id'] ?? null);
        if (! $departmentId && $data['role'] !== 'coordinator') {
            return back()->withErrors(['department_id' => 'Your ID was accepted, but it has no department attached. Please choose your department below.'])->withInput();
        }

        try {
            $user = DB::transaction(function () use ($data, $registered, $departmentId, $idNumber) {
                $user = User::create([
                    'name' => $data['name'], 'email' => $data['email'], 'password' => $data['password'],
                    'role' => $data['role'], 'department_id' => $departmentId, 'student_no' => $idNumber,
                ]);

                $claimed = RegisteredId::whereKey($registered->id)->whereNull('used_by')->update(['used_by' => $user->id, 'used_at' => now()]);
                if (! $claimed) {
                    throw new \RuntimeException('ID already taken');
                }

                return $user;
            });
        } catch (\RuntimeException $e) {
            return back()->withErrors(['id_number' => 'This ID was just registered by someone else.'])->withInput();
        }

        Auth::login($user);
        $request->session()->regenerate();
        ActivityLog::record('register', 'Created account as '.$user->roleLabel().' with ID '.$idNumber, $user->id);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        ActivityLog::record('logout', 'Signed out');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
