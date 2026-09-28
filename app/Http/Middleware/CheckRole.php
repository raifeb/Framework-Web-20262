<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user() || ! in_array($request->user()->role, $roles)) {
            $userRole = $request->user()?->role ?? 'Anda';
            return response()->view('errors.403', [
                'message' => "Role {$userRole} tidak memiliki izin untuk halaman ini."
            ], 403);
        }
        return $next($request);
    }
}