<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Milestone;
use App\Models\Notice;
use App\Models\Phase;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\User;
use Illuminate\Http\Request;

class CoordinatorController extends Controller
{
    /* ---------- Phases & milestones ---------- */

    public function phases()
    {
        return view('coordinator.phases', ['phases' => Phase::with('milestones')->orderBy('position')->get()]);
    }

    public function storePhase(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:150'], 'description' => ['nullable', 'string', 'max:500']]);
        Phase::create($data + ['position' => (Phase::max('position') ?? 0) + 1]);
        ActivityLog::record('phase.create', 'Added phase: '.$data['name']);

        return back()->with('success', 'Phase added.');
    }

    public function destroyPhase(Phase $phase)
    {
        ActivityLog::record('phase.delete', 'Deleted phase: '.$phase->name);
        $phase->delete();

        return back()->with('success', 'Phase deleted along with its milestones.');
    }

    public function storeMilestone(Request $request)
    {
        $data = $request->validate([
            'phase_id' => ['required', 'exists:phases,id'],
            'title' => ['required', 'string', 'max:200'],
            'due_date' => ['nullable', 'date'],
        ]);

        $milestone = Milestone::create($data + ['position' => (Milestone::max('position') ?? 0) + 1]);

        foreach (Project::where('status', '!=', 'completed')->get() as $project) {
            ProjectMilestone::firstOrCreate(
                ['project_id' => $project->id, 'milestone_id' => $milestone->id],
                ['due_date' => $milestone->due_date, 'status' => 'pending']
            );
        }
        ActivityLog::record('milestone.create', 'Added milestone: '.$milestone->title);

        return back()->with('success', 'Milestone added to every active project.');
    }

    public function updateMilestone(Request $request, Milestone $milestone)
    {
        $data = $request->validate(['title' => ['required', 'string', 'max:200'], 'due_date' => ['nullable', 'date']]);
        $milestone->update($data);
        ProjectMilestone::where('milestone_id', $milestone->id)->where('status', '!=', 'approved')->update(['due_date' => $data['due_date'] ?? null]);
        ActivityLog::record('milestone.update', 'Updated milestone: '.$milestone->title);

        return back()->with('success', 'Milestone updated.');
    }

    public function destroyMilestone(Milestone $milestone)
    {
        ActivityLog::record('milestone.delete', 'Deleted milestone: '.$milestone->title);
        $milestone->delete();

        return back()->with('success', 'Milestone deleted.');
    }

    /* ---------- Assignments ---------- */

    public function assignments()
    {
        return view('coordinator.assignments', [
            'projects' => Project::with(['department', 'leader', 'instructor', 'adviser'])->orderBy('department_id')->get(),
            'instructors' => User::where('role', 'instructor')->where('is_active', true)->orderBy('name')->get(),
            'advisers' => User::where('role', 'adviser')->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function assign(Request $request, Project $project)
    {
        $data = $request->validate([
            'instructor_id' => ['nullable', 'exists:users,id'],
            'adviser_id' => ['nullable', 'exists:users,id'],
        ]);

        $project->update(['instructor_id' => $data['instructor_id'] ?? null, 'adviser_id' => $data['adviser_id'] ?? null]);
        Notice::notify([$project->leader_id, $project->instructor_id, $project->adviser_id], 'Project assignment updated', $project->title, route('projects.show', $project));
        ActivityLog::record('project.assign', 'Updated instructor/adviser for: '.$project->title);

        return back()->with('success', 'Assignment saved.');
    }

    /* ---------- Delayed projects ---------- */

    public function delayed()
    {
        $projects = Project::with(['department', 'leader', 'instructor', 'adviser', 'milestones.milestone.phase'])->get()
            ->filter->isDelayed()->values();

        return view('coordinator.delayed', compact('projects'));
    }

    /* ---------- Reports ---------- */

    private function reportRows(Request $request)
    {
        $q = Project::with(['department', 'leader', 'instructor', 'adviser', 'members', 'milestones.milestone.phase']);

        if ($request->filled('department')) {
            $q->where('department_id', $request->department);
        }

        return $q->orderBy('department_id')->get();
    }

    public function reports(Request $request)
    {
        $rows = $this->reportRows($request);

        return view('coordinator.reports', [
            'rows' => $rows,
            'departments' => Department::orderBy('code')->get(),
            'summary' => [
                'total' => $rows->count(),
                'completed' => $rows->where('status', 'completed')->count(),
                'delayed' => $rows->filter->isDelayed()->count(),
                'avg' => $rows->count() ? (int) round($rows->avg(fn ($p) => $p->progress())) : 0,
            ],
        ]);
    }

    public function export(Request $request)
    {
        $rows = $this->reportRows($request);

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Title', 'Type', 'Department', 'Leader', 'Instructor', 'Adviser', 'Members', 'Approved milestones', 'Total milestones', 'Progress %', 'Status']);
            foreach ($rows as $p) {
                fputcsv($out, [
                    $p->title, $p->type, $p->department?->code, $p->leader?->name, $p->instructor?->name, $p->adviser?->name,
                    $p->members->count(), $p->milestones->where('status', 'approved')->count(), $p->milestones->count(),
                    $p->progress(), $p->statusLabel(),
                ]);
            }
            fclose($out);
        }, 'thesiscap-accomplishment-report-'.now()->format('Ymd').'.csv', ['Content-Type' => 'text/csv']);
    }
}
