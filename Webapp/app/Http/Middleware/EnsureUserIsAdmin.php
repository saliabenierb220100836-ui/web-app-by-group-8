<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // 404 (not 403) so the existence of /admin isn't confirmed to non-admins.
        abort_unless($user && $user->isAdmin() && $user->is_active, 404);

        return $next($request);
    }
}
