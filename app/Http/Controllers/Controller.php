<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;

abstract class Controller
{
    protected function user(): User
    {
        return auth()->user();
    }

    /** Abort with 403 unless the signed-in user may see this project. */
    protected function guard(Project $project): Project
    {
        abort_unless(
            Project::visibleTo($this->user())->whereKey($project->getKey())->exists(),
            403,
            'You do not have access to this project.'
        );

        return $project;
    }
}
