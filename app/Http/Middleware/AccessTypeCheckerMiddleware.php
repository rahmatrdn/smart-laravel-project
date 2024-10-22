<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AccessTypeCheckerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $requiredRole): Response
    {
        $userRole = session('access_type');

        if (!$userRole) {
            return redirect('login')->with('error', 'Anda harus login terlebih dahulu.');
        }

        if ($userRole != $requiredRole) {
            abort(404);
        }

        return $next($request);
    }
}
