<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SessionRefreshController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->session()->put('_last_refresh_at', now()->timestamp);
        $request->session()->save();

        return response()->json([
            'csrf_token' => csrf_token(),
            'refreshed_at' => now()->toIso8601String(),
        ]);
    }
}
