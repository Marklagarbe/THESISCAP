<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Notice;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskActivity;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $u = $this->user();
        $projectIds = Project::visibleTo($u)->pluck('id');

        $q = Task::whereIn('project_id', $projectIds)->with(['project', 'assignee', 'milestone.milestone']);

        if ($u->role === 'student') {
            $q->where('assigned_to', $u->id);
        }
        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }

        $tasks = $q->orderByRaw("status = 'done'")->orderBy('due_date')->get();
        $ledProjects = $u->role === 'leader' ? Project::where('leader_id', $u->id)->with(['members', 'milestones.milestone'])->get() : collect();

        return view('tasks.index', compact('tasks', 'ledProjects'));
    }

    public function store(Request $request)
    {
        $u = $this->user();
        $data = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:2000'],
            'assigned_to' => ['required', 'exists:users,id'],
            'due_date' => ['nullable', 'date'],
            'project_milestone_id' => ['nullable', 'exists:project_milestones,id'],
        ]);

        $project = Project::findOrFail($data['project_id']);
        abort_unless($project->leader_id === $u->id, 403, 'Only the project manager can assign tasks.');

        if (! $project->members()->where('users.id', $data['assigned_to'])->exists()) {
            return back()->withErrors(['assigned_to' => 'Pick someone from your group.'])->withInput();
        }
        if (! empty($data['project_milestone_id']) && ! $project->milestones()->whereKey($data['project_milestone_id'])->exists()) {
            return back()->withErrors(['project_milestone_id' => 'That milestone is not part of this project.'])->withInput();
        }

        $task = Task::create($data + ['created_by' => $u->id, 'status' => 'todo']);
        TaskActivity::log($task, 'created', 'Assigned to '.$task->load('assignee')->assignee->name);
        Notice::notify([$task->assigned_to], 'New task assigned', $task->title, route('tasks.index'));
        ActivityLog::record('task.create', 'Assigned task: '.$task->title);

        return back()->with('success', 'Task assigned.');
    }

    public function show(Task $task)
    {
        $this->guard($task->project);
        $task->load(['project.members', 'assignee', 'creator', 'milestone.milestone', 'activities.user']);

        return view('tasks.show', compact('task'));
    }

    public function update(Request $request, Task $task)
    {
        $u = $this->user();
        $project = $task->project;
        $allowed = $u->hasPerm('tasks.update') && ($u->id === $task->assigned_to || $u->id === $project->leader_id);
        abort_unless($allowed, 403, 'You can only update your own tasks.');

        $data = $request->validate(['status' => ['required', 'in:todo,in_progress,done']]);
        $old = $task->status;
        $task->update($data);

        if ($old !== $task->status) {
            $label = fn ($v) => ['todo' => 'To do', 'in_progress' => 'In progress', 'done' => 'Done'][$v] ?? $v;
            TaskActivity::log($task, 'status', $label($old).' to '.$label($task->status));
        }

        if ($task->created_by && $task->created_by !== $u->id) {
            Notice::notify([$task->created_by], 'Task updated', $u->name.' marked "'.$task->title.'" as '.str_replace('_', ' ', $task->status), route('tasks.index'));
        }

        return back()->with('success', 'Task updated.');
    }

    public function destroy(Task $task)
    {
        abort_unless($task->project->leader_id === $this->user()->id, 403);
        $task->delete();
        ActivityLog::record('task.delete', 'Deleted task: '.$task->title);

        return back()->with('success', 'Task deleted.');
    }
}
