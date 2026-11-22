<?php

namespace App\Support;

use App\Models\Notice;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class Reminders
{
    /** Creates due-soon and overdue reminders for one user (at most once per day per item). */
    public static function forUser(User $u): void
    {
        $today = now()->startOfDay();

        $make = function (string $title, string $body, ?string $url) use ($u, $today) {
            $exists = Notice::where('user_id', $u->id)->where('title', $title)->where('body', $body)
                ->where('created_at', '>=', $today)->exists();

            if (! $exists) {
                Notice::create(['user_id' => $u->id, 'title' => $title, 'body' => $body, 'url' => $url]);
            }
        };

        Task::where('assigned_to', $u->id)->where('status', '!=', 'done')->whereNotNull('due_date')
            ->whereDate('due_date', '<=', now()->addDays(2))->get()
            ->each(function ($t) use ($make, $today) {
                $late = $t->due_date->lt($today);
                $make($late ? 'Task overdue' : 'Task due soon', $t->title.' (due '.$t->due_date->format('M j').')', route('tasks.show', $t));
            });

        if ($u->isStudent()) {
            Project::visibleTo($u)->with('milestones.milestone')->get()->each(function ($p) use ($make, $today) {
                foreach ($p->milestones as $pm) {
                    if ($pm->status === 'approved' || ! $pm->due_date || $pm->due_date->gt(now()->addDays(3))) {
                        continue;
                    }
                    $make(
                        $pm->due_date->lt($today) ? 'Milestone overdue' : 'Milestone due soon',
                        $pm->milestone->title.' (due '.$pm->due_date->format('M j').')',
                        route('projects.show', $p)
                    );
                }
            });
        }
    }
}
