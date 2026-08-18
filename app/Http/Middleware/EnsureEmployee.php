<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmployee
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user || ! $user->isEmployee()) {
            if ($user && $user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('login');
        }

        $perm = \App\Support\EmployeeAccess::forRoute($request->route()?->getName() ?? '');
        if ($perm && ! $user->canEmployee($perm)) {
            abort(403, 'Admin ne ye page aapke role me enable nahi kiya. Roles & Permissions → Employee panel me tick karwayein.');
        }

        return $next($request);
    }
}
