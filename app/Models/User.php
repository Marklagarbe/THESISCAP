<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    public const ROLES = [
        'student' => 'Student Member',
        'leader' => 'Student Project Manager',
        'instructor' => 'Instructor',
        'adviser' => 'Adviser',
        'coordinator' => 'Project Coordinator',
        'admin' => 'Administrator',
    ];

    protected $fillable = ['name', 'email', 'password', 'role', 'department_id', 'student_no', 'phone', 'is_active'];

    protected $hidden = ['password', 'remember_token'];

    protected ?array $permCache = null;

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function notices(): HasMany
    {
        return $this->hasMany(Notice::class);
    }

    public function roleLabel(): string
    {
        return self::ROLES[$this->role] ?? ucfirst($this->role);
    }

    public function isStudent(): bool
    {
        return in_array($this->role, ['student', 'leader'], true);
    }

    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim($this->name));
        $first = mb_substr($parts[0] ?? '?', 0, 1);
        $last = count($parts) > 1 ? mb_substr(end($parts), 0, 1) : '';

        return strtoupper($first.$last);
    }

    public function homeRoute(): string
    {
        return match ($this->role) {
            'student' => 'member.home',
            'leader' => 'manager.home',
            'instructor' => 'instructor.home',
            'adviser' => 'adviser.home',
            'coordinator' => 'coordinator.home',
            default => 'admin.home',
        };
    }

    public function hasPerm(string $permission): bool
    {
        if ($this->role === 'admin') {
            return true;
        }

        $this->permCache ??= RolePermission::where('role', $this->role)->pluck('permission')->all();

        return in_array($permission, $this->permCache, true);
    }

    public function unreadNoticeCount(): int
    {
        return $this->notices()->whereNull('read_at')->count();
    }
}
