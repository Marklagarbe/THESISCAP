<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Submission extends Model
{
    protected $fillable = [
        'project_id', 'project_milestone_id', 'user_id', 'type', 'chapter_no', 'title',
        'version', 'file_path', 'original_name', 'status', 'notes',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(ProjectMilestone::class, 'project_milestone_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(Feedback::class)->latest();
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'chapter' => 'Chapter',
            'manuscript' => 'Manuscript',
            default => 'Requirement',
        };
    }
}
