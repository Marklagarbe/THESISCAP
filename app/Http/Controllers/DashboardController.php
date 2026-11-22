<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Feedback;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\Submission;
use App\Models\Task;
use App\Models\User;
use App\Support\Reminders;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            Reminders::forUser($this->user());
        } catch (\Throwable $e) {
            report($e); // reminders must never block sign-in
        }

        return redirect()->route($this->user()->homeRoute());
    }

    /** Student Member landing page. */
    public function member()
    {
        $u = $this->user();
        $projects = Project::visibleTo($u)->with(['department', 'leader', 'milestones.milestone.phase'])->get();
        $tasks = Task::where('assigned_to', $u->id)->with('project')->orderByRaw("status = 'done'")->orderBy('due_date')->get();
        $feedback = Feedback::whereHas('submission.project', fn ($q) => $q->visibleTo($u))
            ->with(['user', 'submission'])->latest()->take(6)->get();
        $mySubmissions = Submission::where('user_id', $u->id)->with('project')->latest()->take(5)->get();
        $done = $tasks->where('status', 'done')->count();
        $personal = $tasks->count() ? (int) round($done / $tasks->count() * 100) : 0;

        return view('dashboards.member', compact('projects', 'tasks', 'feedback', 'mySubmissions', 'personal', 'done'));
    }

    /** Student Project Manager (group leader) landing page. */
    public function manager()
    {
        $u = $this->user();
        $projects = Project::where('leader_id', $u->id)
            ->with(['department', 'members', 'tasks.assignee', 'milestones.milestone.phase', 'instructor', 'adviser'])->get();
        $projectIds = $projects->pluck('id');
        $submissions = Submission::whereIn('project_id', $projectIds)->with(['project', 'author'])->latest()->take(8)->get();
        $deadlines = ProjectMilestone::whereIn('project_id', $projectIds)->where('status', '!=', 'approved')
            ->with(['milestone', 'project'])->orderBy('due_date')->take(6)->get();

        return view('dashboards.manager', compact('projects', 'submissions', 'deadlines'));
    }

    public function instructor()
    {
        $u = $this->user();
        $projects = Project::where('instructor_id', $u->id)->with(['department', 'leader', 'members', 'milestones.milestone.phase'])->get();
        $pending = Submission::whereIn('project_id', $projects->pluck('id'))->whereIn('status', ['submitted', 'under_review'])
            ->with(['project', 'author'])->latest()->take(10)->get();

        return view('dashboards.instructor', compact('projects', 'pending'));
    }

    public function adviser()
    {
        $u = $this->user();
        $projects = Project::where('adviser_id', $u->id)->with(['department', 'leader', 'milestones.milestone.phase'])->get();
        $ids = $projects->pluck('id');
        $chapters = Submission::whereIn('project_id', $ids)->where('type', 'chapter')->whereIn('status', ['submitted', 'under_review'])
            ->with(['project', 'author'])->latest()->take(8)->get();
        $revisions = Submission::whereIn('project_id', $ids)->where('status', 'revision_requested')->with(['project', 'author'])->latest()->take(8)->get();
        $awaiting = ProjectMilestone::whereIn('project_id', $ids)->where('status', 'submitted')->with(['project', 'milestone'])->get();

        return view('dashboards.adviser', compact('projects', 'chapters', 'revisions', 'awaiting'));
    }

    public function coordinator()
    {
        $projects = Project::with(['department', 'leader', 'instructor', 'adviser', 'milestones.milestone.phase'])->get();
        $delayed = $projects->filter->isDelayed();
        $unassigned = $projects->filter(fn ($p) => ! $p->instructor_id || ! $p->adviser_id);
        $byDept = Department::orderBy('code')->get()->map(function ($d) use ($projects) {
            $list = $projects->where('department_id', $d->id);

            return ['dept' => $d, 'count' => $list->count(), 'avg' => $list->count() ? (int) round($list->avg(fn ($p) => $p->progress())) : 0];
        });

        return view('dashboards.coordinator', compact('projects', 'delayed', 'unassigned', 'byDept'));
    }

    public function admin()
    {
        $counts = [
            'users' => User::count(),
            'projects' => Project::count(),
            'submissions' => Submission::count(),
            'inactive' => User::where('is_active', false)->count(),
        ];
        $byRole = collect(User::ROLES)->map(fn ($label, $role) => ['label' => $label, 'count' => User::where('role', $role)->count()]);
        $logs = ActivityLog::with('user')->latest()->take(8)->get();

        return view('dashboards.admin', compact('counts', 'byRole', 'logs'));
    }
}
