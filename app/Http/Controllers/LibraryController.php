<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Project;
use App\Models\Submission;

class LibraryController extends Controller
{
    private function type(string $kind): string
    {
        abort_unless(in_array($kind, ['chapters', 'manuscripts'], true), 404);

        return $kind === 'chapters' ? 'chapter' : 'manuscript';
    }

    /** Department picker: Chapters or Manuscripts grouped by department. */
    public function index(string $kind)
    {
        $type = $this->type($kind);
        $projectIds = Project::visibleTo($this->user())->pluck('id');

        $departments = Department::orderBy('code')->get()->map(function ($d) use ($projectIds, $type) {
            $ids = Project::whereIn('id', $projectIds)->where('department_id', $d->id)->pluck('id');
            $d->item_count = Submission::whereIn('project_id', $ids)->where('type', $type)->count();
            $d->project_count = $ids->count();

            return $d;
        });

        return view('library.index', compact('kind', 'departments'));
    }

    public function department(string $kind, Department $department)
    {
        $type = $this->type($kind);
        $projectIds = Project::visibleTo($this->user())->where('department_id', $department->id)->pluck('id');

        $items = Submission::whereIn('project_id', $projectIds)->where('type', $type)
            ->with(['project', 'author'])->orderBy('chapter_no')->latest()->get()->groupBy('project_id');

        $projects = Project::whereIn('id', $projectIds)->get()->keyBy('id');

        return view('library.department', compact('kind', 'department', 'items', 'projects'));
    }
}
