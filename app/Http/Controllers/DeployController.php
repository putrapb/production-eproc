<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DeployController extends Controller
{
    /**
     * Post-deployment webhook — called by cPanel after git deploy.
     * Runs artisan commands to clear caches after deployment.
     */
    public function postUpdate(Request $request): JsonResponse
    {
        try {
            \Illuminate\Support\Facades\Artisan::call('config:clear');
            \Illuminate\Support\Facades\Artisan::call('view:clear');
            \Illuminate\Support\Facades\Artisan::call('route:clear');

            return response()->json([
                'status'  => 'ok',
                'message' => 'Post-deployment tasks completed successfully.',
                'time'    => now()->toDateTimeString(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
