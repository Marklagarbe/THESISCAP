<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegisteredId extends Model
{
    /** ID type => [prefix, label, roles a person with this ID may register as] */
    public const TYPES = [
        'student' => ['ST', 'Student', ['student', 'leader']],
        'instructor' => ['TE', 'Teacher / Instructor', ['instructor']],
        'adviser' => ['AD', 'Adviser', ['adviser']],
        'coordinator' => ['CO', 'Project Coordinator', ['coordinator']],
    ];

    protected $fillable = ['id_number', 'type', 'full_name', 'department_id', 'used_by', 'used_at'];

    protected $casts = ['used_at' => 'datetime'];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'used_by');
    }

    /** "te - 3346776 " becomes "TE-3346776". */
    public static function normalize(?string $value): string
    {
        return strtoupper(preg_replace('/\s+/', '', (string) $value));
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type][1] ?? ucfirst($this->type);
    }

    public function allowedRoles(): array
    {
        return self::TYPES[$this->type][2] ?? [];
    }
}
