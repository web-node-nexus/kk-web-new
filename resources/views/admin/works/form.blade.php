@extends('layouts.admin')

@section('title', ($mode === 'create' ? 'Add' : 'Edit').' Project | Admin')

@section('content')
@php
    $p = $project;
    $isCreate = $mode === 'create';
@endphp

<div class="kk-pagehead">
    <div>
        <h1>{{ $isCreate ? 'Add project' : 'Edit project' }}</h1>
        <p>Client details, budget, received amount, EMI schedule aur receipt — sab yahi.</p>
    </div>
    <div class="kk-pagehead__actions">
        <a href="{{ route('admin.works.index') }}" class="kk-btn kk-btn-secondary">Back</a>
    </div>
</div>

@if ($errors->any())
    <div style="margin-bottom:16px;background:#fef2f2;color:#991b1b;border:1px solid #fecaca;padding:12px 14px;border-radius:12px">
        {{ $errors->first() }}
    </div>
@endif

<section class="kk-card">
    <div class="kk-card__body">
        <form class="kk-form" method="POST" action="{{ $isCreate ? route('admin.works.store') : route('admin.works.update', $p->id) }}" enctype="multipart/form-data" id="projectForm">
            @csrf
            @if (! $isCreate)
                @method('PUT')
            @endif

            <div class="kk-form-grid">
                <div class="kk-field" style="grid-column:1/-1">
                    <label>Logo / project image</label>
                    <input type="file" name="logo" accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp">
                    @if ($p?->logoUrl())
                        <p class="kk-muted" style="margin:8px 0 0;font-size:12px">Current: <a href="{{ $p->logoUrl() }}" target="_blank">view logo</a></p>
                    @endif
                </div>

                <div class="kk-field">
                    <label>Project name *</label>
                    <input type="text" name="name" value="{{ old('name', $p->name ?? '') }}" required maxlength="190">
                </div>
                <div class="kk-field">
                    <label>Client name *</label>
                    <input type="text" name="client_name" value="{{ old('client_name', $p->client_name ?? '') }}" required maxlength="190">
                </div>
                <div class="kk-field">
                    <label>Mobile no *</label>
                    <input type="tel" name="mobile" value="{{ old('mobile', $p->mobile ?? '') }}" required maxlength="40">
                </div>
                <div class="kk-field">
                    <label>Email ID *</label>
                    <input type="email" name="email" value="{{ old('email', $p->email ?? '') }}" required maxlength="190">
                </div>
                <div class="kk-field" style="grid-column:1/-1">
                    <label>Address</label>
                    <input type="text" name="address" value="{{ old('address', $p->address ?? '') }}" maxlength="500">
                </div>
                <div class="kk-field">
                    <label>Due date</label>
                    <input type="date" name="due_date" value="{{ old('due_date', optional($p?->due_date)->format('Y-m-d')) }}">
                </div>
                <div class="kk-field">
                    <label>Final budget (₹) *</label>
                    <input type="number" step="0.01" min="0" name="final_budget" id="final_budget" value="{{ old('final_budget', $p->final_budget ?? '') }}" required>
                </div>

                @if (! $isCreate)
                    <div class="kk-field">
                        <label>Status</label>
                        <select name="status">
                            @foreach (['active' => 'Active', 'completed' => 'Completed', 'on_hold' => 'On hold'] as $val => $label)
                                <option value="{{ $val }}" @selected(old('status', $p->status) === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="kk-field" style="grid-column:1/-1">
                    <label>Notes</label>
                    <input type="text" name="notes" value="{{ old('notes', $p->notes ?? '') }}" maxlength="1000" placeholder="Optional internal note">
                </div>
            </div>

            @if ($isCreate)
                <hr style="border:0;border-top:1px solid #e2e8f0;margin:8px 0 4px">
                <h3 style="margin:0 0 4px;font-size:15px">Received amount + receipt</h3>
                <p class="kk-muted" style="margin:0 0 12px;font-size:12px">Jo amount abhi receive ho chuki hai woh daalo. Save pe client ko mail jaayegi.</p>
                <div class="kk-form-grid">
                    <div class="kk-field">
                        <label>Received amount (₹)</label>
                        <input type="number" step="0.01" min="0" name="received_amount" id="received_amount" value="{{ old('received_amount', 0) }}">
                    </div>
                    <div class="kk-field">
                        <label>Pending (auto)</label>
                        <input type="text" id="pending_preview" value="₹0.00" readonly style="font-weight:800;color:#b45309;background:#fffbeb;border-color:#fde68a">
                    </div>
                    <div class="kk-field" style="grid-column:1/-1">
                        <label>Receipt upload</label>
                        <input type="file" name="receipt" accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/*">
                        <p class="kk-muted" style="margin:8px 0 0;font-size:12px">Payment screenshot / PDF — max 8 MB.</p>
                    </div>
                </div>

                <hr style="border:0;border-top:1px solid #e2e8f0;margin:16px 0 8px">
                <h3 style="margin:0 0 4px;font-size:15px">EMI / installments</h3>
                <p class="kk-muted" style="margin:0 0 12px;font-size:12px">Pending amount ko monthly EMI me todna ho to yahan schedule banao. Client ko EMI details mail me jaayengi.</p>
                <div class="kk-form-grid">
                    <div class="kk-field">
                        <label>Payment plan</label>
                        <select name="payment_plan" id="payment_plan">
                            <option value="one_time" @selected(old('payment_plan', 'one_time') === 'one_time')>One-time / no EMI</option>
                            <option value="emi" @selected(old('payment_plan') === 'emi')>EMI / installments</option>
                        </select>
                    </div>
                    <div class="kk-field" data-emi-only hidden>
                        <label>Number of EMIs</label>
                        <input type="number" min="2" max="24" id="emi_count" value="{{ old('emi_count', 3) }}">
                    </div>
                    <div class="kk-field" data-emi-only hidden>
                        <label>First EMI date</label>
                        <input type="date" id="emi_start" value="{{ old('emi_start', now()->addMonth()->format('Y-m-d')) }}">
                    </div>
                </div>
                <div id="emiRows" data-emi-only hidden style="margin-top:12px"></div>

                <label style="display:flex;align-items:flex-start;gap:10px;margin-top:16px;font-weight:700;cursor:pointer">
                    <input type="hidden" name="send_mail" value="0">
                    <input type="checkbox" name="send_mail" value="1" checked style="margin-top:3px">
                    <span>
                        Client ko confirmation email bhejo
                        <span class="kk-muted" style="display:block;font-weight:500;margin-top:2px;font-size:12px">Project details, budget, received, pending aur EMI schedule mail me jaayenge.</span>
                    </span>
                </label>
            @endif

            <div class="kk-form-actions">
                <a href="{{ route('admin.works.index') }}" class="kk-btn kk-btn-secondary">Cancel</a>
                <button class="kk-btn kk-btn-primary" type="submit">{{ $isCreate ? 'Save project' : 'Update project' }}</button>
            </div>
        </form>
    </div>
</section>
@endsection

@if ($isCreate)
@push('scripts')
<script>
(() => {
  const budget = document.getElementById('final_budget');
  const received = document.getElementById('received_amount');
  const pending = document.getElementById('pending_preview');
  const plan = document.getElementById('payment_plan');
  const countEl = document.getElementById('emi_count');
  const startEl = document.getElementById('emi_start');
  const rows = document.getElementById('emiRows');
  const emiBlocks = document.querySelectorAll('[data-emi-only]');
  const money = (n) => '₹' + Number(n || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

  const remaining = () => Math.max(0, parseFloat(budget.value || 0) - parseFloat(received.value || 0));

  const paintPending = () => { pending.value = money(remaining()); };

  const toggleEmi = () => {
    const on = plan.value === 'emi';
    emiBlocks.forEach((el) => { el.hidden = !on; });
    if (on) buildRows();
  };

  const buildRows = () => {
    const n = Math.max(2, Math.min(24, parseInt(countEl.value || '3', 10)));
    const rem = remaining();
    const each = n > 0 ? Math.floor((rem / n) * 100) / 100 : 0;
    let acc = 0;
    const start = startEl.value ? new Date(startEl.value + 'T00:00:00') : new Date();
    let html = '<div class="kk-table-wrap"><table class="kk-table"><thead><tr><th>EMI</th><th>Amount (₹)</th><th>Due date</th></tr></thead><tbody>';
    for (let i = 0; i < n; i++) {
      const amt = i === n - 1 ? Math.max(0, Math.round((rem - acc) * 100) / 100) : each;
      acc += amt;
      const d = new Date(start);
      d.setMonth(d.getMonth() + i);
      const iso = [d.getFullYear(), String(d.getMonth() + 1).padStart(2, '0'), String(d.getDate()).padStart(2, '0')].join('-');
      html += `<tr>
        <td><input type="text" name="emi_label[]" value="EMI ${i + 1}"></td>
        <td><input type="number" step="0.01" min="0" name="emi_amount[]" value="${amt.toFixed(2)}"></td>
        <td><input type="date" name="emi_due[]" value="${iso}"></td>
      </tr>`;
    }
    html += '</tbody></table></div>';
    rows.innerHTML = html;
  };

  [budget, received].forEach((el) => el?.addEventListener('input', () => {
    paintPending();
    if (plan.value === 'emi') buildRows();
  }));
  plan?.addEventListener('change', toggleEmi);
  countEl?.addEventListener('input', buildRows);
  startEl?.addEventListener('change', buildRows);
  paintPending();
  toggleEmi();
})();
</script>
@endpush
@endif
