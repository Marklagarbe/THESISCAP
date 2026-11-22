<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Milestone;
use App\Models\Notice;
use App\Models\Feedback;
use App\Models\Project;
use App\Models\ProgressUpdate;
use App\Models\ProjectMilestone;
use App\Models\TaskActivity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $u = $this->user();
        $q = Project::visibleTo($u)->with(['department', 'leader', 'instructor', 'adviser', 'members', 'milestones.milestone.phase']);

        if ($request->filled('department')) {
            $q->where('department_id', $request->department);
        }
        if ($request->filled('q')) {
            $q->where('title', 'like', '%'.$request->q.'%');
        }

        $projects = $q->latest()->get();

        if ($request->filled('status')) {
            $projects = $projects->filter(fn ($p) => $p->statusLabel() === $request->status)->values();
        }

        return view('projects.index', [
            'projects' => $projects,
            'departments' => Department::orderBy('code')->get(),
        ]);
    }

    public function create()
    {
        return view('projects.create', ['departments' => Department::orderBy('code')->get()]);
    }

    public function store(Request $request)
    {
        $u = $this->user();
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:thesis,capstone'],
            'department_id' => ['required', 'exists:departments,id'],
            'abstract' => ['nullable', 'string', 'max:3000'],
            'due_date' => ['nullable', 'date'],
        ]);

        $project = Project::create($data + [
            'leader_id' => $u->id,
            'status' => 'ongoing',
            'start_date' => now(),
        ]);
        $project->members()->attach($u->id);

        foreach (Milestone::all() as $m) {
            ProjectMilestone::create([
                'project_id' => $project->id,
                'milestone_id' => $m->id,
                'due_date' => $m->due_date,
                'status' => 'pending',
            ]);
        }

        $coordinators = User::where('role', 'coordinator')->pluck('id');
        Notice::notify($coordinators, 'New project registered', $project->title, route('projects.show', $project));
        ActivityLog::record('project.create', 'Registered project: '.$project->title);

        return redirect()->route('projects.show', $project)->with('success', 'Project registered. Add your group members next.');
    }

    public function show(Project $project)
    {
        $this->guard($project);
        $project->load(['department', 'leader', 'instructor', 'adviser', 'members', 'tasks.assignee', 'submissions.author', 'milestones.milestone.phase', 'milestones.approver']);

        $updates = $project->progressUpdates()->with(['user', 'task'])->take(30)->get();

        // Project timeline: everything that happened, newest first.
        $events = collect([[
            'at' => $project->created_at, 'icon' => 'flag', 'text' => 'Project registered', 'by' => optional($project->leader)->name,
        ]]);
        foreach ($project->milestones as $pm) {
            if ($pm->approved_at) {
                $events->push(['at' => $pm->approved_at, 'icon' => 'tick', 'text' => 'Milestone approved: '.$pm->milestone->title, 'by' => optional($pm->approver)->name]);
            }
        }
        foreach ($project->submissions as $s) {
            $events->push(['at' => $s->created_at, 'icon' => 'upload', 'text' => 'Submitted '.$s->title.' (version '.$s->version.')', 'by' => optional($s->author)->name]);
        }
        Feedback::whereIn('submission_id', $project->submissions->pluck('id'))->with(['user', 'submission'])->get()->each(function ($f) use ($events) {
            $events->push(['at' => $f->created_at, 'icon' => $f->kind === 'approval' ? 'tick' : 'edit', 'text' => $f->kindLabel().' on '.$f->submission->title, 'by' => optional($f->user)->name]);
        });
        foreach ($updates as $u) {
            $events->push(['at' => $u->created_at, 'icon' => 'activity', 'text' => 'Progress update: '.Str::limit($u->body, 90), 'by' => optional($u->user)->name]);
        }
        TaskActivity::whereIn('task_id', $project->tasks->pluck('id'))->whereIn('action', ['created', 'status'])->with(['user', 'task'])->get()->each(function ($a) use ($events) {
            $events->push(['at' => $a->created_at, 'icon' => 'check', 'text' => 'Task "'.$a->task->title.'": '.($a->action === 'created' ? $a->detail : $a->detail), 'by' => optional($a->user)->name]);
        });
        $events = $events->sortByDesc('at')->take(60)->values();

        return view('projects.show', [
            'project' => $project, 'milestones' => $project->orderedMilestones(), 'updates' => $updates, 'events' => $events,
        ]);
    }

    public function storeUpdate(Request $request, Project $project)
    {
        $u = $this->user();
        abort_unless($project->members()->where('users.id', $u->id)->exists(), 403, 'Only group members can post progress updates.');

        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
            'task_id' => ['nullable', 'exists:tasks,id'],
        ]);

        $task = null;
        if (! empty($data['task_id'])) {
            $task = $project->tasks()->find($data['task_id']);
            abort_unless($task, 422, 'That task is not part of this project.');
        }

        ProgressUpdate::create(['project_id' => $project->id, 'user_id' => $u->id, 'task_id' => $task?->id, 'body' => $data['body']]);
        if ($task) {
            TaskActivity::log($task, 'update', $data['body']);
        }

        Notice::notify([$project->leader_id, $project->instructor_id, $project->adviser_id], 'New progress update', $u->name.': '.Str::limit($data['body'], 80), route('projects.show', $project).'#updates');
        ActivityLog::record('progress.update', 'Posted a progress update on '.$project->title);

        return back()->with('success', 'Progress update posted.');
    }

    public function update(Request $request, Project $project)
    {
        $u = $this->user();
        abort_unless($u->id === $project->leader_id || $u->hasPerm('assignments.manage') || $u->role === 'admin', 403);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'abstract' => ['nullable', 'string', 'max:3000'],
            'due_date' => ['nullable', 'date'],
        ]);
        $project->update($data);
        ActivityLog::record('project.update', 'Updated project: '.$project->title);

        return back()->with('success', 'Project details saved.');
    }

    public function addMember(Request $request, Project $project)
    {
        abort_unless($this->user()->id === $project->leader_id, 403, 'Only the project manager can add members.');

        $request->validate(['email' => ['required', 'email']]);
        $member = User::where('email', $request->email)->whereIn('role', ['student', 'leader'])->first();

        if (! $member) {
            return back()->withErrors(['email' => 'No student account found with that email.']);
        }
        if ($project->members()->where('users.id', $member->id)->exists()) {
            return back()->withErrors(['email' => 'That student is already in the group.']);
        }

        $project->members()->attach($member->id);
        Notice::notify([$member->id], 'You were added to a project', $project->title, route('projects.show', $project));
        ActivityLog::record('project.member_add', $member->name.' added to '.$project->title);

        return back()->with('success', $member->name.' joined the group.');
    }

    public function removeMember(Project $project, User $user)
    {
        abort_unless($this->user()->id === $project->leader_id, 403);
        abort_if($user->id === $project->leader_id, 422, 'The project manager cannot be removed.');

        $project->members()->detach($user->id);
        $project->tasks()->where('assigned_to', $user->id)->update(['assigned_to' => $project->leader_id]);
        ActivityLog::record('project.member_remove', $user->name.' removed from '.$project->title);

        return back()->with('success', 'Member removed. Their tasks moved to you.');
    }
}
