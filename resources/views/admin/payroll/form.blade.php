@extends('layouts.admin')

@section('title', ($mode === 'create' ? 'Add' : 'Edit').' Payroll | Admin')

@section('content')
@php
    $r = $record;
    $empId = old('employee_id', $r->employee_id ?? '');
    $salaryMap = $employees->mapWithKeys(fn ($e) => [$e->id => (float) ($e->salary ?? 0)]);
@endphp

<div class="kk-pagehead">
    <div>
        <h1>{{ $mode === 'create' ? 'Add payroll' : 'Edit payroll' }}</h1>
        <p>Net pay = Basic + Allowances − Deductions (auto calculate).</p>
    </div>
    <div class="kk-pagehead__actions">
        @if ($mode === 'edit' && $r)
            <a href="{{ route('admin.payroll.receipt', $r->id) }}" class="kk-btn kk-btn-secondary" target="_blank">Receipt</a>
        @endif
        <a href="{{ route('admin.payroll.index') }}" class="kk-btn kk-btn-secondary">Back</a>
    </div>
</div>

@if ($errors->any())
    <div style="margin-bottom:16px;background:#fef2f2;color:#991b1b;border:1px solid #fecaca;padding:12px 14px;border-radius:12px">
        {{ $errors->first() }}
    </div>
@endif

<section class="kk-card">
    <div class="kk-card__body">
        <form class="kk-form" method="POST" action="{{ $mode === 'create' ? route('admin.payroll.store') : route('admin.payroll.update', $r->id) }}" id="payrollForm" enctype="multipart/form-data">
            @csrf
            @if ($mode === 'edit')
                @method('PUT')
            @endif

            <div class="kk-form-grid">
                <div class="kk-field" style="grid-column:1/-1">
                    <label>Employee *</label>
                    <input type="search" id="empFilter" placeholder="Search name / email / code…" style="margin-bottom:8px" autocomplete="off">
                    <select name="employee_id" id="employee_id" required @disabled($mode === 'edit')>
                        <option value="">Select employee…</option>
                        @foreach ($employees as $emp)
                            <option
                                value="{{ $emp->id }}"
                                data-salary="{{ (float) ($emp->salary ?? 0) }}"
                                data-search="{{ strtolower($emp->name.' '.$emp->email.' '.$emp->employee_code) }}"
                                @selected((string)$empId === (string)$emp->id)
                            >
                                {{ $emp->name }} — {{ $emp->email }}
                                @if($emp->employee_code) ({{ $emp->employee_code }}) @endif
                                @if($emp->status !== 'active') [{{ $emp->status }}] @endif
                                @if($emp->salary) · base ₹{{ number_format((float)$emp->salary, 0) }} @endif
                            </option>
                        @endforeach
                    </select>
                    <p class="kk-muted" style="margin:8px 0 0;font-size:12px">Save ke baad isi employee ke panel → Payroll me slip dikhegi.</p>
                    @if ($mode === 'edit')
                        <input type="hidden" name="employee_id" value="{{ $r->employee_id }}">
                    @endif
                </div>

                <div class="kk-field">
                    <label>Month *</label>
                    <input type="month" name="month" value="{{ old('month', $defaultMonth) }}" required>
                </div>
                <div class="kk-field">
                    <label>Status *</label>
                    <select name="status" required>
                        @foreach (['pending', 'paid'] as $st)
                            <option value="{{ $st }}" @selected(old('status', $r->status ?? 'pending') === $st)>{{ ucfirst($st) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="kk-field">
                    <label>Basic salary (₹) *</label>
                    <input type="number" step="0.01" min="0" name="basic" id="basic" value="{{ old('basic', $r->basic ?? '') }}" required>
                </div>
                <div class="kk-field">
                    <label>Allowances (₹)</label>
                    <input type="number" step="0.01" min="0" name="allowances" id="allowances" value="{{ old('allowances', $r->allowances ?? 0) }}">
                </div>
                <div class="kk-field">
                    <label>Deductions (₹)</label>
                    <input type="number" step="0.01" min="0" name="deductions" id="deductions" value="{{ old('deductions', $r->deductions ?? 0) }}">
                </div>
                <div class="kk-field">
                    <label>Net pay (auto)</label>
                    <input type="text" id="net_preview" value="₹0.00" readonly style="font-weight:800;color:#0f766e;background:#f0fdfa;border-color:#99f6e4">
                </div>

                <div class="kk-field" style="grid-column:1/-1">
                    <label>Notes (optional)</label>
                    <input type="text" name="notes" value="{{ old('notes', $r->notes ?? '') }}" placeholder="e.g. Includes festival bonus / PF deducted">
                </div>

                <div class="kk-field" style="grid-column:1/-1">
                    <label>Add receipt (optional)</label>
                    <input type="file" name="receipt" accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/*">
                    <p class="kk-muted" style="margin:8px 0 0;font-size:12px">Bank screenshot, UPI proof, ya PDF — max 8 MB (PDF / JPG / PNG).</p>
                    @if ($mode === 'edit' && $r?->hasUploadedReceipt())
                        <p style="margin:10px 0 0;font-size:13px">
                            Current:
                            <a href="{{ $r->receiptFileUrl() }}" target="_blank" rel="noopener">{{ $r->receipt_name ?: 'View receipt' }}</a>
                        </p>
                        <label style="display:flex;align-items:center;gap:8px;margin-top:8px;font-weight:600">
                            <input type="checkbox" name="remove_receipt" value="1">
                            Remove uploaded receipt
                        </label>
                    @endif
                </div>

                <div class="kk-field" style="grid-column:1/-1">
                    <label style="display:flex;align-items:flex-start;gap:10px;font-weight:700;cursor:pointer">
                        <input type="checkbox" name="open_receipt" value="1" style="margin-top:3px" @checked(old('open_receipt', $mode === 'create'))>
                        <span>
                            Save ke baad salary receipt kholo
                            <span class="kk-muted" style="display:block;font-weight:500;margin-top:2px;font-size:12px">Print / PDF ke liye official payslip page open hogi.</span>
                        </span>
                    </label>
                </div>
            </div>

            <div class="kk-form-actions">
                <a href="{{ route('admin.payroll.index') }}" class="kk-btn kk-btn-secondary">Cancel</a>
                <button class="kk-btn kk-btn-primary" type="submit">{{ $mode === 'create' ? 'Save payroll' : 'Update payroll' }}</button>
            </div>
        </form>
    </div>
</section>
@endsection

@push('scripts')
<script>
(() => {
  const basic = document.getElementById('basic');
  const allow = document.getElementById('allowances');
  const ded = document.getElementById('deductions');
  const net = document.getElementById('net_preview');
  const emp = document.getElementById('employee_id');
  const filter = document.getElementById('empFilter');
  const isCreate = {{ $mode === 'create' ? 'true' : 'false' }};

  const calc = () => {
    const b = parseFloat(basic.value || 0);
    const a = parseFloat(allow.value || 0);
    const d = parseFloat(ded.value || 0);
    const n = Math.max(0, b + a - d);
    net.value = '₹' + n.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  };

  emp?.addEventListener('change', () => {
    const opt = emp.selectedOptions[0];
    const sal = parseFloat(opt?.dataset?.salary || 0);
    if (isCreate && sal > 0) {
      basic.value = sal;
    }
    calc();
  });

  filter?.addEventListener('input', () => {
    const q = (filter.value || '').toLowerCase().trim();
    [...emp.options].forEach((opt, i) => {
      if (i === 0) return;
      const hay = opt.dataset.search || opt.textContent.toLowerCase();
      opt.hidden = q !== '' && !hay.includes(q);
    });
  });

  [basic, allow, ded].forEach(el => el?.addEventListener('input', calc));
  calc();
})();
</script>
@endpush
