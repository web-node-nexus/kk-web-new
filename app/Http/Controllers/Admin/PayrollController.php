<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\PayrollRecord;
use App\Services\TaskNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PayrollController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->has('status')
            ? ($request->string('status')->toString() ?: 'all')
            : 'all';
        $q = trim((string) $request->string('q'));
        $month = trim((string) $request->string('month'));

        $base = PayrollRecord::query();

        $counts = [
            'all' => (clone $base)->count(),
            'pending' => (clone $base)->where('status', 'pending')->count(),
            'paid' => (clone $base)->where('status', 'paid')->count(),
        ];

        $items = PayrollRecord::query()
            ->with('employee.user')
            ->when($status !== 'all', fn ($qr) => $qr->where('status', $status))
            ->when($month !== '', fn ($qr) => $qr->where('month', $month))
            ->when($q !== '', function ($qr) use ($q) {
                $like = '%'.$q.'%';
                $qr->whereHas('employee', function ($e) use ($like) {
                    $e->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('employee_code', 'like', $like);
                });
            })
            ->orderByDesc('month')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $totals = [
            'net' => (clone $items->getCollection())->sum(fn ($r) => (float) $r->net_pay),
            'paid_net' => PayrollRecord::query()->where('status', 'paid')
                ->when($month !== '', fn ($qr) => $qr->where('month', $month))
                ->sum('net_pay'),
        ];

        return view('admin.payroll.index', [
            'user' => Auth::user(),
            'items' => $items,
            'filters' => compact('status', 'q', 'month'),
            'counts' => $counts,
            'totals' => $totals,
        ]);
    }

    public function create(): View
    {
        return view('admin.payroll.form', [
            'user' => Auth::user(),
            'record' => null,
            'mode' => 'create',
            'employees' => $this->employeesForForm(),
            'defaultMonth' => \App\Support\AppTime::now()->format('Y-m'),
        ]);
    }

    public function store(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $data = $this->validated($request);
        $record = $this->persist($request, $data);

        return $this->afterSave(
            $request,
            $record,
            'Payroll saved for '.$record->employee?->name.'. The slip is now visible in the employee panel.'
        );
    }

    public function edit(int $id): View
    {
        $record = PayrollRecord::query()->with('employee')->findOrFail($id);

        return view('admin.payroll.form', [
            'user' => Auth::user(),
            'record' => $record,
            'mode' => 'edit',
            'employees' => $this->employeesForForm(),
            'defaultMonth' => $record->month,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $record = PayrollRecord::query()->findOrFail($id);
        $data = $this->validated($request, $record);
        $record = $this->persist($request, $data, $record);

        return $this->afterSave(
            $request,
            $record,
            'Payroll updated for '.$record->employee?->name.'. The employee panel shows the new amount.'
        );
    }

    public function markPaid(int $id): RedirectResponse
    {
        $record = PayrollRecord::query()->with('employee')->findOrFail($id);
        $record->update([
            'status' => 'paid',
            'paid_at' => $record->paid_at ?: now(),
        ]);
        $this->notifyEmployee($record->fresh(['employee']), 'paid');

        return back()->with('success', 'Marked paid: '.$record->employee?->name.' · '.$record->monthLabel().'. Employee ko notification gayi.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $record = PayrollRecord::query()->findOrFail($id);
        $this->deleteReceiptFile($record);
        $record->delete();

        return back()->with('success', 'Payroll record deleted.');
    }

    public function receipt(int $id): View
    {
        $record = PayrollRecord::query()->with(['employee.department'])->findOrFail($id);

        return view('admin.payroll.receipt', [
            'user' => Auth::user(),
            'record' => $record,
            'employee' => $record->employee,
            'company' => [
                'name' => \App\Support\SiteSettings::get('company_name', 'KK Digital Solution'),
                'email' => \App\Support\SiteSettings::get('support_email', 'support.kkdigitalsolution@gmail.com'),
                'phone' => \App\Support\SiteSettings::get('support_phone', '+91 93709 21363'),
                'address' => \App\Support\SiteSettings::get('company_address', 'K & K Hub, Jalgaon Jamod, Maharashtra, 443402'),
            ],
        ]);
    }

    /** @return array<string, mixed> */
    protected function validated(Request $request, ?PayrollRecord $record = null): array
    {
        $data = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'month' => array_values(array_filter([
                'required',
                'regex:/^\d{4}-\d{2}$/',
                $record
                    ? Rule::unique('payroll_records', 'month')
                        ->where(fn ($q) => $q->where('employee_id', $request->input('employee_id')))
                        ->ignore($record->id)
                    : null,
            ])),
            'basic' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'allowances' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'deductions' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'status' => ['required', 'in:pending,paid'],
            'notes' => ['nullable', 'string', 'max:500'],
            'issue_date' => ['nullable', 'date'],
            'receipt' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:8192'],
            'remove_receipt' => ['nullable', 'boolean'],
            'open_receipt' => ['nullable'],
        ], [
            'month.unique' => 'Payroll for this employee and month already exists. Edit the existing record.',
            'basic.max' => 'Basic amount too large. Max ₹9,99,99,999.99',
            'receipt.mimes' => 'Receipt must be PDF, JPG, PNG, or WEBP.',
            'receipt.max' => 'Receipt file max 8 MB.',
        ]);

        $data['allowances'] = (float) ($data['allowances'] ?? 0);
        $data['deductions'] = (float) ($data['deductions'] ?? 0);
        $data['basic'] = (float) $data['basic'];
        $data['issue_date'] = filled($data['issue_date'] ?? null) ? $data['issue_date'] : null;
        unset($data['receipt'], $data['remove_receipt'], $data['open_receipt']);

        return $data;
    }

    protected function afterSave(Request $request, PayrollRecord $record, string $message): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        if ($request->boolean('open_receipt')) {
            return \App\Support\Ajax::ok($request, $message, route('admin.payroll.receipt', $record->id));
        }

        return \App\Support\Ajax::ok($request, $message, route('admin.payroll.index'));
    }

    /** @param  array<string, mixed>  $data */
    protected function persist(Request $request, array $data, ?PayrollRecord $record = null): PayrollRecord
    {
        $data['net_pay'] = round(max(0, (float) $data['basic'] + (float) $data['allowances'] - (float) $data['deductions']), 2);

        $target = $record;
        if (! $target) {
            $target = PayrollRecord::query()
                ->where('employee_id', $data['employee_id'])
                ->where('month', $data['month'])
                ->first();
        }

        if (($data['status'] ?? '') === 'paid') {
            $data['paid_at'] = $target?->paid_at ?: now();
        } else {
            $data['paid_at'] = null;
        }

        $kind = $target ? 'updated' : 'created';
        if ($target) {
            $target->update($data);
            $target = $target->fresh(['employee']);
        } else {
            $target = PayrollRecord::query()->create($data);
            $target->load('employee');
        }

        $this->storeReceiptFile($request, $target);
        $this->notifyEmployee($target, $kind);

        return $target->fresh(['employee']);
    }

    protected function storeReceiptFile(Request $request, PayrollRecord $record): void
    {
        if ($request->boolean('remove_receipt') && $record->receipt_path) {
            $this->deleteReceiptFile($record);
            $record->update(['receipt_path' => null, 'receipt_name' => null]);
        }

        if (! $request->hasFile('receipt')) {
            return;
        }

        $this->deleteReceiptFile($record);
        $file = $request->file('receipt');
        $path = $file->store('payroll-receipts', 'public');
        $record->update([
            'receipt_path' => $path,
            'receipt_name' => $file->getClientOriginalName(),
        ]);
    }

    protected function deleteReceiptFile(PayrollRecord $record): void
    {
        if ($record->receipt_path) {
            Storage::disk('public')->delete($record->receipt_path);
        }
    }

    protected function notifyEmployee(PayrollRecord $record, string $kind): void
    {
        $employee = $record->employee;
        if (! $employee) {
            return;
        }

        $net = '₹'.number_format((float) $record->net_pay, 2);
        $month = $record->monthLabel();
        [$title, $body] = match ($kind) {
            'paid' => ['Salary marked paid', $month.' · '.$net.' credited. Payroll page pe slip dekho.'],
            'updated' => ['Payslip updated', $month.' · Net '.$net],
            default => ['New payslip published', $month.' · Net '.$net.'. Payroll page pe kholo.'],
        };

        TaskNotifier::panel($employee, 'payroll', $title, $body, '/employee/payroll');
    }

    protected function employeesForForm()
    {
        return Employee::query()
            ->with('user')
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get();
    }
}
