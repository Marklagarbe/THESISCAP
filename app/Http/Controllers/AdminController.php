<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Project;
use App\Models\RegisteredId;
use App\Models\RolePermission;
use App\Models\Setting;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /* ---------- Users ---------- */

    public function users(Request $request)
    {
        $q = User::with('department')->orderBy('name');

        if ($request->filled('role')) {
            $q->where('role', $request->role);
        }
        if ($request->filled('q')) {
            $q->where(fn ($w) => $w->where('name', 'like', '%'.$request->q.'%')->orWhere('email', 'like', '%'.$request->q.'%'));
        }

        return view('admin.users', ['users' => $q->paginate(15)->withQueryString(), 'departments' => Department::orderBy('code')->get()]);
    }

    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'department_id' => ['nullable', 'exists:departments,id'],
            'student_no' => ['nullable', 'string', 'max:40'],
            'password' => ['required', 'string', 'min:8'],
        ]);
        $user = User::create($data);
        ActivityLog::record('user.create', 'Created account: '.$user->email);

        return back()->with('success', 'Account created.');
    }

    public function updateUser(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'department_id' => ['nullable', 'exists:departments,id'],
            'student_no' => ['nullable', 'string', 'max:40'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        if ($user->id === $this->user()->id && $data['role'] !== 'admin') {
            return back()->withErrors(['role' => 'You cannot remove your own administrator role.']);
        }
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $user->update($data);
        ActivityLog::record('user.update', 'Updated account: '.$user->email);

        return back()->with('success', 'Account updated.');
    }

    public function toggleUser(User $user)
    {
        abort_if($user->id === $this->user()->id, 422, 'You cannot disable your own account.');
        $user->update(['is_active' => ! $user->is_active]);
        ActivityLog::record('user.toggle', ($user->is_active ? 'Enabled ' : 'Disabled ').$user->email);

        return back()->with('success', $user->is_active ? 'Account enabled.' : 'Account disabled.');
    }

    public function destroyUser(User $user)
    {
        abort_if($user->id === $this->user()->id, 422, 'You cannot delete your own account.');
        ActivityLog::record('user.delete', 'Deleted account: '.$user->email);
        $user->delete();

        return back()->with('success', 'Account deleted.');
    }

    /* ---------- Registered IDs ---------- */

    public function ids(Request $request)
    {
        $q = RegisteredId::with(['department', 'user'])->orderByRaw('used_by is not null')->orderBy('id_number');

        if ($request->filled('type')) {
            $q->where('type', $request->type);
        }
        if ($request->status === 'available') {
            $q->whereNull('used_by');
        } elseif ($request->status === 'used') {
            $q->whereNotNull('used_by');
        }
        if ($request->filled('q')) {
            $q->where(fn ($w) => $w->where('id_number', 'like', '%'.strtoupper($request->q).'%')->orWhere('full_name', 'like', '%'.$request->q.'%'));
        }

        return view('admin.ids', [
            'ids' => $q->paginate(20)->withQueryString(),
            'departments' => Department::orderBy('code')->get(),
            'total' => RegisteredId::count(),
            'used' => RegisteredId::whereNotNull('used_by')->count(),
        ]);
    }

    private function validId(string $id, string $type): bool
    {
        $prefix = RegisteredId::TYPES[$type][0] ?? null;

        return $prefix !== null && (bool) preg_match('/^'.$prefix.'-\d{4,10}$/', $id);
    }

    public function storeId(Request $request)
    {
        $data = $request->validate([
            'id_number' => ['required', 'string', 'max:30'],
            'type' => ['required', 'in:'.implode(',', array_keys(RegisteredId::TYPES))],
            'full_name' => ['nullable', 'string', 'max:150'],
            'department_id' => ['nullable', 'exists:departments,id'],
        ]);
        $data['id_number'] = RegisteredId::normalize($data['id_number']);

        if (! $this->validId($data['id_number'], $data['type'])) {
            return back()->withErrors(['id_number' => 'A '.strtolower(RegisteredId::TYPES[$data['type']][1]).' ID must look like '.RegisteredId::TYPES[$data['type']][0].'-1234567 (prefix, dash, 4 to 10 digits).'])->withInput();
        }
        if (RegisteredId::where('id_number', $data['id_number'])->exists()) {
            return back()->withErrors(['id_number' => 'That ID is already in the list.'])->withInput();
        }

        RegisteredId::create($data);
        ActivityLog::record('id.create', 'Added registered ID: '.$data['id_number']);

        return back()->with('success', 'ID added.');
    }

    /** Bulk add: one per line as "ID" or "ID, Full name". */
    public function importIds(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', 'in:'.implode(',', array_keys(RegisteredId::TYPES))],
            'department_id' => ['nullable', 'exists:departments,id'],
            'lines' => ['required', 'string', 'max:100000'],
        ]);

        $added = 0;
        $skipped = [];
        foreach (preg_split('/\r\n|\r|\n/', $data['lines']) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $parts = array_map('trim', explode(',', $line, 2));
            $id = RegisteredId::normalize($parts[0]);

            if (! $this->validId($id, $data['type']) || RegisteredId::where('id_number', $id)->exists()) {
                $skipped[] = $parts[0];
                continue;
            }
            RegisteredId::create([
                'id_number' => $id, 'type' => $data['type'], 'full_name' => $parts[1] ?? null, 'department_id' => $data['department_id'] ?? null,
            ]);
            $added++;
        }
        ActivityLog::record('id.import', 'Imported '.$added.' registered ID(s)');

        $message = $added.' ID(s) added.'.($skipped ? ' Skipped (invalid or already listed): '.implode(', ', array_slice($skipped, 0, 8)).(count($skipped) > 8 ? '...' : '') : '');

        return back()->with('success', $message);
    }

    public function destroyId(RegisteredId $registeredId)
    {
        if ($registeredId->used_by) {
            return back()->withErrors(['id_number' => 'This ID belongs to an account. Delete or edit the account instead.']);
        }
        ActivityLog::record('id.delete', 'Deleted registered ID: '.$registeredId->id_number);
        $registeredId->delete();

        return back()->with('success', 'ID deleted.');
    }

    /* ---------- Roles & permissions ---------- */

    public function roles()
    {
        $granted = RolePermission::all()->groupBy('role')->map(fn ($g) => $g->pluck('permission')->all());

        return view('admin.roles', [
            'roles' => collect(User::ROLES)->except('admin'),
            'permissions' => config('thesiscap.permissions'),
            'granted' => $granted,
        ]);
    }

    public function saveRoles(Request $request)
    {
        $matrix = $request->input('perm', []);
        $valid = array_keys(config('thesiscap.permissions'));

        foreach (array_keys(User::ROLES) as $role) {
            if ($role === 'admin') {
                continue;
            }
            RolePermission::where('role', $role)->delete();
            foreach ((array) ($matrix[$role] ?? []) as $perm) {
                if (in_array($perm, $valid, true)) {
                    RolePermission::create(['role' => $role, 'permission' => $perm]);
                }
            }
        }
        ActivityLog::record('roles.update', 'Updated role permissions');

        return back()->with('success', 'Permissions saved.');
    }

    /* ---------- Settings ---------- */

    public function settings()
    {
        $keys = ['site_name' => 'ThesisCap', 'school_name' => '', 'academic_year' => '', 'allow_registration' => '1', 'max_upload_mb' => '20'];
        $values = [];
        foreach ($keys as $k => $default) {
            $values[$k] = Setting::get($k, $default);
        }

        return view('admin.settings', ['values' => $values, 'departments' => Department::withCount('users', 'projects')->orderBy('code')->get()]);
    }

    public function saveSettings(Request $request)
    {
        $data = $request->validate([
            'site_name' => ['required', 'string', 'max:80'],
            'school_name' => ['nullable', 'string', 'max:150'],
            'academic_year' => ['nullable', 'string', 'max:20'],
            'max_upload_mb' => ['required', 'integer', 'between:1,100'],
        ]);
        foreach ($data as $k => $v) {
            Setting::put($k, $v ?? '');
        }
        Setting::put('allow_registration', $request->boolean('allow_registration') ? '1' : '0');
        ActivityLog::record('settings.update', 'Updated system settings');

        return back()->with('success', 'Settings saved.');
    }

    public function storeDepartment(Request $request)
    {
        $data = $request->validate(['code' => ['required', 'string', 'max:20', 'unique:departments,code'], 'name' => ['required', 'string', 'max:150']]);
        $data['code'] = strtoupper($data['code']);
        Department::create($data);
        ActivityLog::record('department.create', 'Added department: '.$data['code']);

        return back()->with('success', 'Department added.');
    }

    public function destroyDepartment(Department $department)
    {
        if ($department->projects()->exists() || $department->users()->exists()) {
            return back()->withErrors(['department' => 'This department still has users or projects.']);
        }
        ActivityLog::record('department.delete', 'Deleted department: '.$department->code);
        $department->delete();

        return back()->with('success', 'Department deleted.');
    }

    /* ---------- Records ---------- */

    public function records()
    {
        return view('admin.records', [
            'projects' => Project::with(['department', 'leader'])->latest()->take(25)->get(),
            'submissions' => Submission::with(['project', 'author'])->latest()->take(25)->get(),
        ]);
    }

    public function destroyProject(Project $project)
    {
        foreach ($project->submissions as $s) {
            if ($s->file_path) {
                Storage::delete($s->file_path);
            }
        }
        ActivityLog::record('project.delete', 'Deleted project: '.$project->title);
        $project->delete();

        return back()->with('success', 'Project and its records deleted.');
    }

    public function destroySubmission(Submission $submission)
    {
        if ($submission->file_path) {
            Storage::delete($submission->file_path);
        }
        ActivityLog::record('submission.delete', 'Deleted submission: '.$submission->title);
        $submission->delete();

        return back()->with('success', 'Submission deleted.');
    }

    /* ---------- System reports & logs ---------- */

    public function reports()
    {
        $projects = Project::with('milestones')->get();

        return view('admin.reports', [
            'byRole' => collect(User::ROLES)->map(fn ($label, $role) => ['label' => $label, 'count' => User::where('role', $role)->count()]),
            'byDept' => Department::orderBy('code')->get()->map(fn ($d) => [
                'code' => $d->code,
                'users' => $d->users()->count(),
                'projects' => $projects->where('department_id', $d->id)->count(),
                'submissions' => Submission::whereHas('project', fn ($q) => $q->where('department_id', $d->id))->count(),
            ]),
            'bySubmission' => Submission::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'projectStatus' => [
                'ongoing' => $projects->filter(fn ($p) => $p->statusLabel() === 'ongoing')->count(),
                'delayed' => $projects->filter(fn ($p) => $p->statusLabel() === 'delayed')->count(),
                'completed' => $projects->where('status', 'completed')->count(),
            ],
            'logins' => ActivityLog::where('action', 'login')->where('created_at', '>=', now()->subDays(7))->count(),
        ]);
    }

    public function logs(Request $request)
    {
        $q = ActivityLog::with('user')->latest();

        if ($request->filled('q')) {
            $q->where(fn ($w) => $w->where('action', 'like', '%'.$request->q.'%')->orWhere('description', 'like', '%'.$request->q.'%'));
        }

        return view('admin.logs', ['logs' => $q->paginate(25)->withQueryString()]);
    }
}
