<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMilestone extends Model
{
    protected $fillable = ['project_id', 'milestone_id', 'status', 'due_date', 'approved_by', 'approved_at', 'remarks'];

    protected $casts = ['due_date' => 'date', 'approved_at' => 'datetime'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(Milestone::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isDelayed(): bool
    {
        return $this->status !== 'approved'
            && $this->due_date !== null
            && $this->due_date->lt(now()->startOfDay());
    }

    public function daysLate(): int
    {
        return $this->isDelayed() ? (int) $this->due_date->diffInDays(now()->startOfDay()) : 0;
    }
}
