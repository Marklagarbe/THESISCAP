<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    protected $fillable = ['user_id', 'title', 'body', 'url', 'read_at'];

    protected $casts = ['read_at' => 'datetime'];

    /** Send an in-app notification to one or more user ids (skips the current user). */
    public static function notify($userIds, string $title, ?string $body = null, ?string $url = null): void
    {
        $me = auth()->id();

        collect($userIds)->flatten()->filter()->unique()->reject(fn ($id) => $id == $me)->each(
            fn ($id) => static::create(['user_id' => $id, 'title' => $title, 'body' => $body, 'url' => $url])
        );
    }
}
