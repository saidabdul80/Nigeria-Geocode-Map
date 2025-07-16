<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, $role, $permission = null)
    {
        if (!$request->user()->hasRole($role)) {
            abort(403);
        }
        
        if ($permission && !$request->user()->hasPermission($permission)) {
            abort(403);
        }
        
        return $next($request);
    }
}