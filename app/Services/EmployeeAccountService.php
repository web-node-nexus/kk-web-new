<?php

namespace App\Services;

use App\Mail\EmployeeWelcomeMail;
use App\Models\AdminRole;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EmployeeAccountService
{
    public const DEFAULT_PASSWORD = 'Employee@123';

    /**
     * Create or refresh login for an employee.
     *
     * @return array{user:?User,password:?string,created:bool,mailed:bool,error?:string}
     */
    public static function provision(
        Employee $employee,
        ?string $plainPassword = null,
        bool $resetPassword = false,
        bool $sendWelcomeMail = true,
    ): array {
        $email = strtolower(trim((string) $employee->email));
        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['user' => null, 'password' => null, 'created' => false, 'mailed' => false];
        }

        $user = User::query()->where('employee_id', $employee->id)->first()
            ?? User::query()->where('email', $email)->where('role', User::ROLE_EMPLOYEE)->first();

        $plain = null;
        $created = false;

        if (! $user) {
            $existing = User::query()->where('email', $email)->first();
            if ($existing && $existing->isAdmin()) {
                return ['user' => null, 'password' => null, 'created' => false, 'mailed' => false, 'error' => 'email_is_admin'];
            }

            $plain = filled($plainPassword) ? $plainPassword : self::DEFAULT_PASSWORD;
            $user = User::query()->create([
                'name' => $employee->name,
                'email' => $email,
                'password' => Hash::make($plain),
                'role' => User::ROLE_EMPLOYEE,
                'employee_id' => $employee->id,
            ]);
            $created = true;
        } else {
            $user->fill([
                'name' => $employee->name,
                'email' => $email,
                'role' => User::ROLE_EMPLOYEE,
                'employee_id' => $employee->id,
            ]);

            if ($resetPassword || filled($plainPassword)) {
                $plain = filled($plainPassword) ? $plainPassword : self::DEFAULT_PASSWORD;
                $user->password = Hash::make($plain);
            }
            $user->save();
        }

        $employeeRoleId = AdminRole::employeeRole()?->id;
        if ($employeeRoleId && ! $user->admin_role_id) {
            $user->update(['admin_role_id' => $employeeRoleId]);
            AdminRole::employeeRole()?->refreshUsersCount();
        }

        $mailed = false;
        if ($sendWelcomeMail && $plain) {
            $mailed = self::sendWelcome($employee->loadMissing('department'), $plain);
        }

        return ['user' => $user, 'password' => $plain, 'created' => $created, 'mailed' => $mailed];
    }

    public static function sendWelcome(Employee $employee, string $plainPassword): bool
    {
        try {
            Mail::to($employee->email)->send(
                new EmployeeWelcomeMail(
                    $employee,
                    $plainPassword,
                    \App\Support\PublicUrl::to('/login'),
                )
            );

            return true;
        } catch (Throwable $e) {
            Log::warning('Employee welcome mail failed', [
                'employee_id' => $employee->id,
                'email' => $employee->email,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
