<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Project extends Model
{
    protected $fillable = [
        'title', 'abstract', 'type', 'department_id', 'leader_id', 'instructor_id',
        'adviser_id', 'status', 'start_date', 'due_date',
    ];

    protected $casts = ['start_date' => 'date', 'due_date' => 'date'];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function adviser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adviser_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')->withTimestamps();
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(ProjectMilestone::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function progressUpdates(): HasMany
    {
        return $this->hasMany(ProgressUpdate::class)->latest();
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->hasPerm('projects.view_all')) {
            return $query;
        }

        return $query->where(function ($w) use ($user) {
            $w->where('leader_id', $user->id)
                ->orWhere('instructor_id', $user->id)
                ->orWhere('adviser_id', $user->id)
                ->orWhereHas('members', fn ($m) => $m->where('users.id', $user->id));
        });
    }

    /** Milestones sorted by phase order, then milestone order. */
    public function orderedMilestones(): Collection
    {
        $this->loadMissing('milestones.milestone.phase');

        return $this->milestones
            ->filter(fn ($m) => $m->milestone && $m->milestone->phase)
            ->sortBy(fn ($m) => sprintf('%05d%05d', $m->milestone->phase->position, $m->milestone->position))
            ->values();
    }

    public function progress(): int
    {
        $this->loadMissing('milestones');
        $total = $this->milestones->count();

        if ($total === 0) {
            return 0;
        }

        return (int) round($this->milestones->where('status', 'approved')->count() / $total * 100);
    }

    public function delayedMilestones(): Collection
    {
        $this->loadMissing('milestones');

        return $this->milestones->filter(fn ($m) => $m->isDelayed())->values();
    }

    public function isDelayed(): bool
    {
        return $this->status !== 'completed' && $this->delayedMilestones()->isNotEmpty();
    }

    public function currentMilestone(): ?ProjectMilestone
    {
        return $this->orderedMilestones()->first(fn ($m) => $m->status !== 'approved');
    }

    public function statusLabel(): string
    {
        if ($this->status === 'completed') {
            return 'completed';
        }

        return $this->isDelayed() ? 'delayed' : 'ongoing';
    }

    /** Re-evaluates completion after a milestone changes. */
    public function refreshStatus(): void
    {
        $total = $this->milestones()->count();
        $approved = $this->milestones()->where('status', 'approved')->count();

        $this->update(['status' => ($total > 0 && $total === $approved) ? 'completed' : 'ongoing']);
    }
}
