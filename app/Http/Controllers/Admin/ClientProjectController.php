<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientProject;
use App\Models\ClientProjectInstallment;
use App\Models\ClientProjectReceipt;
use App\Services\ClientProjectMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ClientProjectController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->has('status')
            ? ($request->string('status')->toString() ?: 'all')
            : 'all';
        $q = trim((string) $request->string('q'));

        $base = ClientProject::query();
        $counts = [
            'all' => (clone $base)->count(),
            'active' => (clone $base)->where('status', 'active')->count(),
            'completed' => (clone $base)->where('status', 'completed')->count(),
        ];

        $items = ClientProject::query()
            ->withSum(['installments as received_sum' => fn ($qr) => $qr->where('status', 'paid')], 'amount')
            ->when($status !== 'all', fn ($qr) => $qr->where('status', $status))
            ->when($q !== '', function ($qr) use ($q) {
                $like = '%'.$q.'%';
                $qr->where(function ($inner) use ($like) {
                    $inner->where('name', 'like', $like)
                        ->orWhere('client_name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('mobile', 'like', $like);
                });
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $budgetTotal = (clone $base)->sum('final_budget');
        $receivedTotal = ClientProjectInstallment::query()->where('status', 'paid')->sum('amount');

        return view('admin.works.index', [
            'user' => Auth::user(),
            'items' => $items,
            'filters' => compact('status', 'q'),
            'counts' => $counts,
            'totals' => [
                'budget' => (float) $budgetTotal,
                'received' => (float) $receivedTotal,
                'pending' => max(0, (float) $budgetTotal - (float) $receivedTotal),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.works.form', [
            'user' => Auth::user(),
            'project' => null,
            'mode' => 'create',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedProject($request);
        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('project-logos', 'public');
        }
        $data['created_by'] = Auth::id();
        $data['status'] = 'active';

        $project = ClientProject::query()->create($data);

        $received = (float) $request->input('received_amount', 0);
        $receipt = $request->file('receipt');
        if ($received > 0) {
            $this->addInstallment($project, [
                'label' => 'Amount received',
                'amount' => $received,
                'due_date' => $project->due_date?->toDateString(),
                'status' => 'paid',
                'paid_at' => now(),
                'notes' => 'Recorded while adding project',
            ], $receipt);
        } elseif ($receipt) {
            $path = $receipt->store('project-receipts', 'public');
            ClientProjectReceipt::query()->create([
                'client_project_id' => $project->id,
                'file_path' => $path,
                'file_name' => $receipt->getClientOriginalName(),
            ]);
        }

        if ($request->input('payment_plan') === 'emi') {
            $this->storeEmiRows($request, $project, $received);
        }

        $project->syncStatus();
        $project->refresh()->load('installments');

        $mailOk = $request->boolean('send_mail')
            ? ClientProjectMailer::created($project)
            : false;

        $msg = 'Project saved: '.$project->name.'.';
        $msg .= $mailOk
            ? ' Client ko confirmation email chali gayi.'
            : ($request->boolean('send_mail') ? ' Email nahi gayi — mail settings / address check karo.' : '');

        return redirect()->route('admin.works.show', $project->id)->with('success', $msg);
    }

    public function show(int $id): View
    {
        $project = ClientProject::query()
            ->with(['installments', 'receipts'])
            ->findOrFail($id);

        return view('admin.works.show', [
            'user' => Auth::user(),
            'project' => $project,
        ]);
    }

    public function edit(int $id): View
    {
        $project = ClientProject::query()->findOrFail($id);

        return view('admin.works.form', [
            'user' => Auth::user(),
            'project' => $project,
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $project = ClientProject::query()->findOrFail($id);
        $data = $this->validatedProject($request, false);
        if ($request->hasFile('logo')) {
            if ($project->logo_path) {
                Storage::disk('public')->delete($project->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('project-logos', 'public');
        }
        $project->update($data);
        $project->syncStatus();

        return redirect()->route('admin.works.show', $project->id)->with('success', 'Project details updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $project = ClientProject::query()->with(['installments', 'receipts'])->findOrFail($id);
        foreach ($project->installments as $row) {
            if ($row->receipt_path) {
                Storage::disk('public')->delete($row->receipt_path);
            }
        }
        foreach ($project->receipts as $row) {
            Storage::disk('public')->delete($row->file_path);
        }
        if ($project->logo_path) {
            Storage::disk('public')->delete($project->logo_path);
        }
        $project->delete();

        return redirect()->route('admin.works.index')->with('success', 'Project deleted.');
    }

    public function recordPayment(Request $request, int $id): RedirectResponse
    {
        $project = ClientProject::query()->findOrFail($id);
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'notes' => ['nullable', 'string', 'max:500'],
            'receipt' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:8192'],
        ]);

        $row = $this->addInstallment($project, [
            'label' => 'Amount received',
            'amount' => (float) $data['amount'],
            'due_date' => now()->toDateString(),
            'status' => 'paid',
            'paid_at' => now(),
            'notes' => $data['notes'] ?? null,
        ], $request->file('receipt'));

        $project->syncStatus();
        $project->refresh()->load('installments');

        $mailOk = $request->boolean('send_mail')
            ? ClientProjectMailer::payment($project, (float) $data['amount'], $row)
            : false;

        return back()->with('success', 'Received amount saved.'.($mailOk ? ' Client ko payment email chali gayi.' : ''));
    }

    public function remindPending(int $id): RedirectResponse
    {
        $project = ClientProject::query()->with('installments')->findOrFail($id);
        if ($project->pendingAmount() <= 0) {
            return back()->with('success', 'Is project pe koi pending amount nahi hai.');
        }

        $ok = ClientProjectMailer::pendingReminder($project);

        return back()->with('success', $ok
            ? 'Pending amount reminder client ko email ho gayi.'
            : 'Reminder email nahi gayi. Email address / mail settings check karo.');
    }

    public function storeInstallment(Request $request, int $id): RedirectResponse
    {
        $project = ClientProject::query()->findOrFail($id);
        $data = $request->validate([
            'label' => ['nullable', 'string', 'max:120'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $this->addInstallment($project, [
            'label' => $data['label'] ?: null,
            'amount' => (float) $data['amount'],
            'due_date' => $data['due_date'] ?? null,
            'status' => 'pending',
            'notes' => $data['notes'] ?? null,
        ]);

        $project->refresh()->load('installments');
        $mailOk = $request->boolean('send_mail')
            ? ClientProjectMailer::emiAdded($project)
            : false;

        return back()->with('success', 'EMI / installment add ho gayi.'.($mailOk ? ' Schedule email client ko chali gayi.' : ''));
    }

    public function payInstallment(Request $request, int $id, int $installmentId): RedirectResponse
    {
        $project = ClientProject::query()->findOrFail($id);
        $row = $project->installments()->whereKey($installmentId)->firstOrFail();
        $request->validate([
            'receipt' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:8192'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $updates = [
            'status' => 'paid',
            'paid_at' => $row->paid_at ?: now(),
        ];
        if ($request->filled('notes')) {
            $updates['notes'] = $request->string('notes')->toString();
        }
        $this->attachReceiptToInstallment($project, $row, $request->file('receipt'), $updates);
        $project->syncStatus();
        $project->refresh()->load('installments');
        $row = $row->fresh();

        $mailOk = $request->boolean('send_mail')
            ? ClientProjectMailer::payment($project, (float) $row->amount, $row)
            : false;

        return back()->with('success', $row->displayLabel().' marked received.'.($mailOk ? ' Client ko email chali gayi.' : ''));
    }

    public function remindInstallment(int $id, int $installmentId): RedirectResponse
    {
        $project = ClientProject::query()->with('installments')->findOrFail($id);
        $row = $project->installments()->whereKey($installmentId)->firstOrFail();
        if ($row->status === 'paid') {
            return back()->with('success', 'Ye installment already received hai.');
        }

        $ok = ClientProjectMailer::emiReminder($project, $row);

        return back()->with('success', $ok
            ? $row->displayLabel().' reminder email client ko chali gayi.'
            : 'EMI reminder email nahi gayi.');
    }

    public function destroyInstallment(int $id, int $installmentId): RedirectResponse
    {
        $project = ClientProject::query()->findOrFail($id);
        $row = $project->installments()->whereKey($installmentId)->firstOrFail();
        if ($row->receipt_path) {
            Storage::disk('public')->delete($row->receipt_path);
        }
        $row->delete();
        $project->syncStatus();

        return back()->with('success', 'Installment deleted.');
    }

    public function storeReceipt(Request $request, int $id): RedirectResponse
    {
        $project = ClientProject::query()->findOrFail($id);
        $data = $request->validate([
            'receipt' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:8192'],
            'amount' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);
        $file = $request->file('receipt');
        $path = $file->store('project-receipts', 'public');
        ClientProjectReceipt::query()->create([
            'client_project_id' => $project->id,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'amount' => $data['amount'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return back()->with('success', 'Receipt upload ho gayi.');
    }

    public function destroyReceipt(int $id, int $receiptId): RedirectResponse
    {
        $project = ClientProject::query()->findOrFail($id);
        $row = $project->receipts()->whereKey($receiptId)->firstOrFail();
        Storage::disk('public')->delete($row->file_path);
        $row->delete();

        return back()->with('success', 'Receipt deleted.');
    }

    /** @return array<string, mixed> */
    protected function validatedProject(Request $request, bool $requireCore = true): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'client_name' => ['required', 'string', 'max:190'],
            'mobile' => ['required', 'string', 'max:40'],
            'email' => ['required', 'email', 'max:190'],
            'address' => ['nullable', 'string', 'max:500'],
            'due_date' => ['nullable', 'date'],
            'final_budget' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'status' => ['nullable', 'in:active,completed,on_hold'],
        ]);

        $data['final_budget'] = (float) $data['final_budget'];
        if (! $requireCore) {
            unset($data['status']);
            if ($request->filled('status')) {
                $data['status'] = $request->input('status');
            }
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function addInstallment(ClientProject $project, array $payload, ?UploadedFile $receipt = null): ClientProjectInstallment
    {
        $row = $project->installments()->create([
            'number' => $project->nextInstallmentNumber(),
            'label' => $payload['label'] ?? null,
            'amount' => $payload['amount'],
            'due_date' => $payload['due_date'] ?? null,
            'status' => $payload['status'] ?? 'pending',
            'paid_at' => $payload['paid_at'] ?? null,
            'notes' => $payload['notes'] ?? null,
        ]);

        $this->attachReceiptToInstallment($project, $row, $receipt);

        return $row->fresh();
    }

    /**
     * @param  array<string, mixed>  $updates
     */
    protected function attachReceiptToInstallment(
        ClientProject $project,
        ClientProjectInstallment $row,
        ?UploadedFile $receipt = null,
        array $updates = [],
    ): void {
        if ($receipt) {
            if ($row->receipt_path) {
                Storage::disk('public')->delete($row->receipt_path);
            }
            $path = $receipt->store('project-receipts', 'public');
            $updates['receipt_path'] = $path;
            $updates['receipt_name'] = $receipt->getClientOriginalName();
        }
        if ($updates !== []) {
            $row->update($updates);
        }
    }

    protected function storeEmiRows(Request $request, ClientProject $project, float $alreadyReceived): void
    {
        $amounts = $request->input('emi_amount', []);
        $dates = $request->input('emi_due', []);
        $labels = $request->input('emi_label', []);
        if (! is_array($amounts)) {
            return;
        }

        foreach ($amounts as $i => $raw) {
            $amount = (float) $raw;
            if ($amount <= 0) {
                continue;
            }
            $this->addInstallment($project, [
                'label' => (is_array($labels) ? ($labels[$i] ?? null) : null) ?: ('EMI '.($i + 1)),
                'amount' => $amount,
                'due_date' => is_array($dates) ? ($dates[$i] ?? null) : null,
                'status' => 'pending',
            ]);
        }
    }
}
