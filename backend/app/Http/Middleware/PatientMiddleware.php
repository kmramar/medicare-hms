<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PatientMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()->role !== 'patient') {
            return response()->json(['message' => 'Unauthorized. Patient access required.'], 403);
        }

        return $next($request);
    }
}
