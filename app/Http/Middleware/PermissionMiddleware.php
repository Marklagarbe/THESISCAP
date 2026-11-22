<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        $user = $request->user();

        if (! $user || ! $user->hasPerm($permission)) {
            abort(403, 'Your role does not have permission to do this.');
        }

        return $next($request);
    }
}
