<?php

namespace App\Support;

use App\Models\User;

class Nav
{
    /** Sidebar items for a user: [label, url, icon, active-pattern]. */
    public static function for(User $u): array
    {
        $library = [
            ['Chapters', route('library.index', 'chapters'), 'book', 'library/chapters*'],
            ['Manuscripts', route('library.index', 'manuscripts'), 'file', 'library/manuscripts*'],
        ];
        $tail = [['Notifications', route('notices.index'), 'bell', 'notifications*'], ['Profile', route('profile.edit'), 'user', 'profile*']];

        $items = match ($u->role) {
            'student' => [
                ['Member Home', route('member.home'), 'home', 'member'],
                ['My Projects', route('projects.index'), 'folder', 'projects*'],
                ['My Tasks', route('tasks.index'), 'check', 'tasks*'],
                ['Submissions', route('submissions.index'), 'upload', 'submissions*'],
            ],
            'leader' => [
                ['Manager Home', route('manager.home'), 'home', 'manager'],
                ['Projects', route('projects.index'), 'folder', 'projects*'],
                ['Group Tasks', route('tasks.index'), 'check', 'tasks*'],
                ['Submissions', route('submissions.index'), 'upload', 'submissions*'],
            ],
            'instructor' => [
                ['Dashboard', route('instructor.home'), 'home', 'instructor'],
                ['Assigned Groups', route('projects.index'), 'users', 'projects*'],
                ['Submissions', route('submissions.index'), 'upload', 'submissions*'],
                ['Tasks', route('tasks.index'), 'check', 'tasks*'],
            ],
            'adviser' => [
                ['Dashboard', route('adviser.home'), 'home', 'adviser'],
                ['Advised Projects', route('projects.index'), 'folder', 'projects*'],
                ['Submissions', route('submissions.index'), 'upload', 'submissions*'],
                ['Tasks', route('tasks.index'), 'check', 'tasks*'],
            ],
            'coordinator' => [
                ['Dashboard', route('coordinator.home'), 'home', 'coordinator'],
                ['All Projects', route('projects.index'), 'folder', 'projects*'],
                ['Phases & Milestones', route('coordinator.phases'), 'layers', 'coordinator/phases*'],
                ['Assignments', route('coordinator.assignments'), 'users', 'coordinator/assignments*'],
                ['Delayed Projects', route('coordinator.delayed'), 'alert', 'coordinator/delayed*'],
                ['Reports', route('coordinator.reports'), 'chart', 'coordinator/reports*'],
                ['Submissions', route('submissions.index'), 'upload', 'submissions*'],
            ],
            default => [
                ['Dashboard', route('admin.home'), 'home', 'admin'],
                ['Users', route('admin.users'), 'users', 'admin/users*'],
                ['Registered IDs', route('admin.ids'), 'file', 'admin/ids*'],
                ['Roles & Permissions', route('admin.roles'), 'shield', 'admin/roles*'],
                ['Projects', route('projects.index'), 'folder', 'projects*'],
                ['Records', route('admin.records'), 'database', 'admin/records*'],
                ['System Reports', route('admin.reports'), 'chart', 'admin/reports*'],
                ['Activity Logs', route('admin.logs'), 'activity', 'admin/logs*'],
                ['Settings', route('admin.settings'), 'settings', 'admin/settings*'],
            ],
        };

        return array_merge($items, $library, $tail);
    }
}
