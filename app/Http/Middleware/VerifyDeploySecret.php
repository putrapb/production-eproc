<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * C-1 Security: Protect the deploy webhook endpoint with a shared secret.
 * Caller must send header: X-Deploy-Secret: {DEPLOY_SECRET dari .env}
 */
class VerifyDeploySecret
{
    public function handle(Request $request, Closure $next): Response
    {
        $secret = config('app.deploy_secret');

        if (! $secret || $request->header('X-Deploy-Secret') !== $secret) {
            abort(403, 'Unauthorized webhook call.');
        }

        return $next($request);
    }
}
