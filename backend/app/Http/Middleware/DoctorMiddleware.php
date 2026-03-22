<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DoctorMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()->role !== 'doctor') {
            return response()->json(['message' => 'Unauthorized. Doctor access required.'], 403);
        }

        return $next($request);
    }
}
