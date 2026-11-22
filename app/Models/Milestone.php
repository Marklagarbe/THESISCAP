<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Milestone extends Model
{
    protected $fillable = ['phase_id', 'title', 'description', 'position', 'due_date'];

    protected $casts = ['due_date' => 'date'];

    public function phase(): BelongsTo
    {
        return $this->belongsTo(Phase::class);
    }
}
