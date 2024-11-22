<?php

namespace App\Http\Middleware;

use App\Http\Library\HttpResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth; 

class CheckAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Auth::guard('staff')->user();
        if ($request->user()->role == "admin") {
            return $next($request);
        }

        return HttpResponse::respondUnAuthenticated();
        // return response()->json([
        //     'status' => false,
        //     'message' => 'sai',
        // ], 500);
    }
}
