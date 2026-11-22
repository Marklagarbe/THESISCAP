<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Notice;
use App\Models\ProjectMilestone;
use Illuminate\Http\Request;

class MilestoneController extends Controller
{
    public function update(Request $request, ProjectMilestone $pm)
    {
        $u = $this->user();
        $project = $this->guard($pm->project);
        $request->validate(['action' => ['required', 'in:start,submit,approve,revise'], 'remarks' => ['nullable', 'string', 'max:1000']]);
        $action = $request->action;

        $isLeader = $u->id === $project->leader_id;
        $isStaff = in_array($u->id, [$project->instructor_id, $project->adviser_id], true) || $u->hasPerm('projects.view_all');

        if (in_array($action, ['start', 'submit'], true)) {
            abort_unless($isLeader || $u->role === 'admin', 403, 'Only the project manager can move a milestone forward.');
            $pm->update(['status' => $action === 'start' ? 'in_progress' : 'submitted']);
            if ($action === 'submit') {
                Notice::notify([$project->instructor_id, $project->adviser_id], 'Milestone ready for approval', $pm->milestone->title.' - '.$project->title, route('projects.show', $project));
            }
        } elseif ($action === 'approve') {
            abort_unless($u->hasPerm('milestones.approve') && $isStaff, 403, 'Only the adviser can approve milestones.');
            $pm->update(['status' => 'approved', 'approved_by' => $u->id, 'approved_at' => now(), 'remarks' => $request->remarks]);
            Notice::notify([$project->leader_id], 'Milestone approved', $pm->milestone->title, route('projects.show', $project));
        } else {
            abort_unless($u->hasPerm('submissions.review') && $isStaff, 403, 'Only the instructor or adviser can request revisions.');
            $pm->update(['status' => 'revision', 'approved_by' => null, 'approved_at' => null, 'remarks' => $request->remarks]);
            Notice::notify([$project->leader_id], 'Revision requested on a milestone', $pm->milestone->title, route('projects.show', $project));
        }

        $project->refreshStatus();
        ActivityLog::record('milestone.'.$action, $pm->milestone->title.' ('.$project->title.')');

        return back()->with('success', 'Milestone updated.');
    }
}
