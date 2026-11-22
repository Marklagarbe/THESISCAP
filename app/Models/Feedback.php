<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    protected $table = 'feedback';

    protected $fillable = ['submission_id', 'user_id', 'kind', 'body'];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function kindLabel(): string
    {
        return match ($this->kind) {
            'recommendation' => 'Recommendation',
            'revision_request' => 'Revision requested',
            'approval' => 'Approved',
            'under_review' => 'Marked under review',
            default => 'Feedback',
        };
    }
}
