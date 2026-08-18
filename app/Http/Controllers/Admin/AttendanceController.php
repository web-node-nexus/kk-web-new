<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $tz = 'Asia/Kolkata';
        $date = $request->filled('date')
            ? Carbon::parse($request->string('date'))->timezone($tz)->startOfDay()
            : Carbon::today($tz);

        $monthInput = trim((string) $request->input('month', ''));
        $month = $monthInput !== ''
            ? Carbon::createFromFormat('Y-m', $monthInput, $tz)->startOfMonth()
            : $date->copy()->startOfMonth();
        $monthEnd = $month->copy()->endOfMonth();

        $q = trim((string) $request->string('q'));
        $employeeId = $request->integer('employee_id');

        $allEmployees = Employee::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'employee_code', 'role_title']);

        $selectedEmployee = $employeeId
            ? Employee::query()->find($employeeId)
            : null;

        $employees = Employee::query()
            ->where('status', 'active')
            ->when($q !== '', function ($query) use ($q) {
                $like = '%'.$q.'%';
                $query->where(function ($inner) use ($like) {
                    $inner->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('employee_code', 'like', $like)
                        ->orWhere('role_title', 'like', $like);
                });
            })
            ->when($selectedEmployee, fn ($query) => $query->where('id', $selectedEmployee->id))
            ->orderBy('name')
            ->get();

        $todayRows = AttendanceRecord::query()
            ->whereDate('date', $date->toDateString())
            ->whereIn('employee_id', $employees->pluck('id'))
            ->get()
            ->keyBy('employee_id');

        $board = $employees->map(function (Employee $employee) use ($todayRows) {
            $row = $todayRows->get($employee->id);

            return [
                'employee' => $employee,
                'record' => $row,
                'status' => $this->displayStatus($row),
            ];
        });

        $present = $board->whereIn('status', ['present', 'late', 'checked_out'])->count();
        $absent = $board->where('status', 'not_marked')->count();
        $onLeave = $board->where('status', 'leave')->count();

        $monthRows = collect();
        $monthDays = collect();
        $monthStats = [
            'present' => 0,
            'late' => 0,
            'leave' => 0,
            'absent' => 0,
            'not_marked' => 0,
            'working' => 0,
        ];

        if ($selectedEmployee) {
            $monthRows = AttendanceRecord::query()
                ->where('employee_id', $selectedEmployee->id)
                ->whereBetween('date', [$month->toDateString(), $monthEnd->toDateString()])
                ->get()
                ->keyBy(fn (AttendanceRecord $row) => optional($row->date)->toDateString());

            $cursor = $month->copy();
            while ($cursor->lte($monthEnd)) {
                $key = $cursor->toDateString();
                $rec = $monthRows->get($key);
                $status = $this->displayStatus($rec);
                $isWeekend = $cursor->isWeekend();
                $monthDays->push([
                    'date' => $cursor->copy(),
                    'record' => $rec,
                    'status' => $status,
                    'weekend' => $isWeekend,
                ]);
                if (! $isWeekend) {
                    $monthStats['working']++;
                    if (in_array($status, ['present', 'checked_out'], true)) {
                        $monthStats['present']++;
                    } elseif ($status === 'late') {
                        $monthStats['late']++;
                    } elseif ($status === 'leave') {
                        $monthStats['leave']++;
                    } elseif ($status === 'absent') {
                        $monthStats['absent']++;
                    } else {
                        $monthStats['not_marked']++;
                    }
                }
                $cursor->addDay();
            }
        }

        $history = AttendanceRecord::query()
            ->with('employee')
            ->when($selectedEmployee, fn ($query) => $query->where('employee_id', $selectedEmployee->id))
            ->when(! $selectedEmployee && $q !== '', function ($query) use ($q) {
                $like = '%'.$q.'%';
                $query->whereHas('employee', function ($inner) use ($like) {
                    $inner->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('employee_code', 'like', $like);
                });
            })
            ->when($selectedEmployee, function ($query) use ($month, $monthEnd) {
                $query->whereBetween('date', [$month->toDateString(), $monthEnd->toDateString()]);
            })
            ->latest('date')
            ->latest('id')
            ->paginate(31)
            ->withQueryString();

        return view('admin.attendance.index', [
            'user' => Auth::user(),
            'date' => $date,
            'month' => $month,
            'q' => $q,
            'employeeId' => $employeeId,
            'allEmployees' => $allEmployees,
            'selectedEmployee' => $selectedEmployee,
            'board' => $board,
            'monthDays' => $monthDays,
            'monthStats' => $monthStats,
            'history' => $history,
            'stats' => [
                'total' => $board->count(),
                'present' => $present,
                'not_marked' => $absent,
                'leave' => $onLeave,
            ],
        ]);
    }

    protected function displayStatus(?AttendanceRecord $row): string
    {
        if (! $row) {
            return 'not_marked';
        }
        if ($row->status === 'leave') {
            return 'leave';
        }
        if ($row->status === 'absent') {
            return 'absent';
        }
        if ($row->check_in && $row->check_out) {
            return 'checked_out';
        }
        if ($row->status === 'late') {
            return 'late';
        }
        if ($row->check_in) {
            return 'present';
        }

        return $row->status ?: 'not_marked';
    }
}
