<?php

namespace App\Http\Middleware;

use App\Support\AdminPermissions;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user || ! $user->isAdmin()) {
            if ($user && $user->isEmployee()) {
                return redirect()->route('employee.dashboard');
            }

            return redirect()->route('login');
        }

        $user->loadMissing('adminRole');

        $routeName = $request->route()?->getName() ?? '';

        if (str_starts_with($routeName, 'admin.module.')) {
            $module = (string) $request->route('module');
            if ($module === 'roles') {
                return redirect()->route('admin.roles.index');
            }
            if ($module === 'attendance') {
                return redirect()->route('admin.attendance.index');
            }
            if ($module === 'leaves') {
                return redirect()->route('admin.leaves.index');
            }
            if ($module === 'payroll') {
                return redirect()->route('admin.payroll.index');
            }
            if ($module === 'contacts') {
                return redirect()->route('admin.contacts.index');
            }
            if ($module === 'announcements') {
                return redirect()->route('admin.announcements.index');
            }
            if ($module === 'tasks') {
                return redirect()->route('admin.tasks.index');
            }
            if (in_array($module, ['chat', 'live-chat'], true)) {
                return redirect()->route('admin.chat.index');
            }
            if (in_array($module, ['newsletter', 'tickets'], true)) {
                return redirect()->route('admin.settings');
            }
            if ($module !== '') {
                abort_unless(
                    $user->hasPermission(AdminPermissions::forModule($module)),
                    403,
                    'You do not have permission to open this page.'
                );
            }
        } else {
            $permission = AdminPermissions::forNamedRoute($routeName);
            if ($permission) {
                abort_unless(
                    $user->hasPermission($permission),
                    403,
                    'You do not have permission to open this page.'
                );
            }
        }

        return $next($request);
    }
}
