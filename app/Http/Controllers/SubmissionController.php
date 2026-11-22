<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Feedback;
use App\Models\Notice;
use App\Models\Project;
use App\Models\Setting;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SubmissionController extends Controller
{
    public function index(Request $request)
    {
        $u = $this->user();
        $projectIds = Project::visibleTo($u)->pluck('id');
        $q = Submission::whereIn('project_id', $projectIds)->with(['project.department', 'author']);

        if ($request->filled('type')) {
            $q->where('type', $request->type);
        }
        if ($request->filled('status')) {
            $q->where('status', $request->status);
        } elseif (in_array($u->role, ['instructor', 'adviser'], true) && ! $request->has('all')) {
            $q->whereIn('status', ['submitted', 'under_review']);
        }

        return view('submissions.index', ['submissions' => $q->latest()->paginate(15)->withQueryString()]);
    }

    public function create()
    {
        $projects = Project::visibleTo($this->user())->whereHas('members', fn ($m) => $m->where('users.id', $this->user()->id))
            ->with('milestones.milestone')->get();

        return view('submissions.create', compact('projects'));
    }

    public function store(Request $request)
    {
        $u = $this->user();
        $maxMb = (int) Setting::get('max_upload_mb', 20);

        $data = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'type' => ['required', 'in:chapter,manuscript,requirement'],
            'chapter_no' => ['nullable', 'integer', 'between:1,10'],
            'title' => ['required', 'string', 'max:200'],
            'project_milestone_id' => ['nullable', 'exists:project_milestones,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'file' => ['required', 'file', 'mimes:pdf,doc,docx,ppt,pptx,zip,xls,xlsx', 'max:'.($maxMb * 1024)],
        ]);

        $project = Project::findOrFail($data['project_id']);
        abort_unless($project->members()->where('users.id', $u->id)->exists(), 403, 'You are not a member of this project.');

        $pm = null;
        if (! empty($data['project_milestone_id'])) {
            $pm = $project->milestones()->find($data['project_milestone_id']);
            if (! $pm) {
                return back()->withErrors(['project_milestone_id' => 'That milestone is not part of this project.'])->withInput();
            }
        }

        $file = $request->file('file');
        $version = Submission::where('project_id', $project->id)->where('type', $data['type'])->where('title', $data['title'])->count() + 1;

        $submission = Submission::create([
            'project_id' => $project->id,
            'project_milestone_id' => $pm?->id,
            'user_id' => $u->id,
            'type' => $data['type'],
            'chapter_no' => $data['type'] === 'chapter' ? ($data['chapter_no'] ?? null) : null,
            'title' => $data['title'],
            'version' => $version,
            'file_path' => $file->store('submissions'),
            'original_name' => $file->getClientOriginalName(),
            'status' => 'submitted',
            'notes' => $data['notes'] ?? null,
        ]);

        if ($pm && in_array($pm->status, ['pending', 'in_progress', 'revision'], true)) {
            $pm->update(['status' => 'submitted']);
        }

        Notice::notify([$project->instructor_id, $project->adviser_id], 'New submission to review', $submission->title.' ('.$project->title.')', route('submissions.show', $submission));
        ActivityLog::record('submission.create', 'Submitted: '.$submission->title);

        return redirect()->route('submissions.show', $submission)->with('success', 'Submitted. Your instructor and adviser were notified.');
    }

    public function show(Submission $submission)
    {
        $this->guard($submission->project);
        $submission->load(['project.department', 'project.instructor', 'project.adviser', 'author', 'feedback.user', 'milestone.milestone']);

        $versions = Submission::where('project_id', $submission->project_id)
            ->where('type', $submission->type)->where('title', $submission->title)
            ->with('author')->orderByDesc('version')->get();

        return view('submissions.show', compact('submission', 'versions'));
    }

    public function download(Submission $submission)
    {
        $this->guard($submission->project);
        abort_unless($submission->file_path && Storage::exists($submission->file_path), 404, 'No file is attached to this submission.');

        return Storage::download($submission->file_path, $submission->original_name);
    }

    public function review(Request $request, Submission $submission)
    {
        $u = $this->user();
        $project = $this->guard($submission->project);

        $isStaffOnProject = in_array($u->id, [$project->instructor_id, $project->adviser_id], true) || $u->role === 'admin';
        abort_unless($u->hasPerm('submissions.review') && $isStaffOnProject, 403, 'Only the assigned instructor or adviser can review this.');

        $data = $request->validate([
            'kind' => ['required', 'in:comment,recommendation,revision_request,approval,under_review'],
            'body' => ['nullable', 'string', 'max:4000'],
        ]);
        $kind = $data['kind'];

        if (in_array($kind, ['comment', 'recommendation', 'revision_request'], true) && blank($data['body'] ?? null)) {
            return back()->withErrors(['body' => 'Write your feedback first.'])->withInput();
        }
        if ($kind === 'approval' && ! $u->hasPerm('milestones.approve')) {
            abort(403, 'Only advisers can approve.');
        }

        Feedback::create(['submission_id' => $submission->id, 'user_id' => $u->id, 'kind' => $kind, 'body' => $data['body'] ?? null]);

        $pm = $submission->milestone;
        if ($kind === 'revision_request') {
            $submission->update(['status' => 'revision_requested']);
            $pm?->update(['status' => 'revision', 'remarks' => $data['body']]);
        } elseif ($kind === 'approval') {
            $submission->update(['status' => 'approved']);
            $pm?->update(['status' => 'approved', 'approved_by' => $u->id, 'approved_at' => now()]);
        } elseif ($kind === 'under_review') {
            $submission->update(['status' => 'under_review']);
        }
        $project->refreshStatus();

        $title = match ($kind) {
            'revision_request' => 'Revision requested',
            'approval' => 'Submission approved',
            'recommendation' => 'New recommendation',
            'under_review' => 'Submission under review',
            default => 'New feedback',
        };
        Notice::notify(array_merge([$submission->user_id, $project->leader_id], $project->members()->pluck('users.id')->all()), $title, $submission->title, route('submissions.show', $submission));
        ActivityLog::record('submission.'.$kind, $title.': '.$submission->title);

        return back()->with('success', 'Saved.');
    }
}
