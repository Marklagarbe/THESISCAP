<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskActivity extends Model
{
    protected $fillable = ['task_id', 'user_id', 'action', 'detail'];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function label(): string
    {
        return match ($this->action) {
            'created' => 'Task created',
            'status' => 'Status changed',
            'update' => 'Progress update',
            default => ucfirst($this->action),
        };
    }

    /** Log an activity on a task as the signed-in user. */
    public static function log(Task $task, string $action, ?string $detail = null): void
    {
        static::create(['task_id' => $task->id, 'user_id' => auth()->id(), 'action' => $action, 'detail' => $detail]);
    }
}
