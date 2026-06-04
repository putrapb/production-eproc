<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * Usage in routes:
     *   ->middleware('role:requester')
     *   ->middleware('role:pfa,department_head')
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  One or more allowed roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! in_array($user->role, $roles)) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk halaman ini.');
        }

        return $next($request);
    }
}
