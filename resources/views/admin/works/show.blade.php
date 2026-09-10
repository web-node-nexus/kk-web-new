@extends('layouts.admin')

@section('title', $project->name.' | Project')

@section('content')
@php
    $money = fn ($n) => '₹'.number_format((float) $n, 2);
    $p = $project;
    $received = $p->receivedAmount();
    $pending = $p->pendingAmount();
@endphp

<div class="kk-pagehead">
    <div>
        <h1>{{ $p->name }}</h1>
        <p>{{ $p->code() }} · {{ $p->client_name }} · {{ $p->mobile }}</p>
    </div>
    <div class="kk-pagehead__actions">
        <a href="{{ route('admin.works.edit', $p->id) }}" class="kk-btn kk-btn-secondary">Edit</a>
        <a href="{{ route('admin.works.index') }}" class="kk-btn kk-btn-secondary">Back</a>
        <form method="POST" action="{{ route('admin.works.destroy', $p->id) }}" onsubmit="return confirm('Delete this project?')">
            @csrf
            @method('DELETE')
            <button class="kk-btn kk-btn-danger" type="submit">Delete</button>
        </form>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:16px">
    <div class="kk-card"><div class="kk-card__body" style="padding:14px 16px">
        <div class="kk-muted" style="font-size:12px;font-weight:700">Final budget</div>
        <div style="font-size:22px;font-weight:800;margin-top:6px">{{ $money($p->final_budget) }}</div>
    </div></div>
    <div class="kk-card"><div class="kk-card__body" style="padding:14px 16px">
        <div class="kk-muted" style="font-size:12px;font-weight:700">Received</div>
        <div style="font-size:22px;font-weight:800;margin-top:6px;color:#0f766e">{{ $money($received) }}</div>
    </div></div>
    <div class="kk-card"><div class="kk-card__body" style="padding:14px 16px">
        <div class="kk-muted" style="font-size:12px;font-weight:700">Pending</div>
        <div style="font-size:22px;font-weight:800;margin-top:6px;color:#b45309">{{ $money($pending) }}</div>
    </div></div>
</div>

<div class="kk-grid-2">
    <section class="kk-card">
        <div class="kk-card__body">
            <h2 style="margin:0 0 14px;font-size:16px">Client & project</h2>
            <div style="display:flex;gap:14px;align-items:center;margin-bottom:16px">
                @if ($p->logoUrl())
                    <img src="{{ $p->logoUrl() }}" alt="" style="width:64px;height:64px;border-radius:12px;object-fit:cover;border:1px solid #e2e8f0">
                @endif
                <div>
                    <strong>{{ $p->name }}</strong>
                    <div class="kk-muted" style="font-size:13px">{{ $p->code() }} · {{ ucfirst($p->status) }}</div>
                </div>
            </div>
            <dl class="kk-kv">
                <dt>Client</dt><dd>{{ $p->client_name }}</dd>
                <dt>Mobile</dt><dd>{{ $p->mobile }}</dd>
                <dt>Email</dt><dd>{{ $p->email }}</dd>
                <dt>Address</dt><dd>{{ $p->address ?: '—' }}</dd>
                <dt>Due date</dt><dd>{{ $p->due_date?->format('d M Y') ?: '—' }}</dd>
                <dt>Notes</dt><dd>{{ $p->notes ?: '—' }}</dd>
            </dl>
        </div>
    </section>

    <section class="kk-card">
        <div class="kk-card__body">
            <h2 style="margin:0 0 10px;font-size:16px">Record received amount</h2>
            <p class="kk-muted" style="margin:0 0 12px;font-size:12px">When amount is received, the client gets an email. Receipt is optional.</p>
            <form class="kk-form" method="POST" action="{{ route('admin.works.payment', $p->id) }}" enctype="multipart/form-data">
                @csrf
                <div class="kk-field">
                    <label>Amount (₹) *</label>
                    <input type="number" step="0.01" min="0.01" name="amount" required>
                </div>
                <div class="kk-field">
                    <label>Receipt</label>
                    <input type="file" name="receipt" accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/*">
                </div>
                <div class="kk-field">
                    <label>Notes</label>
                    <input type="text" name="notes" maxlength="500">
                </div>
                <label style="display:flex;gap:8px;align-items:center;font-size:13px;font-weight:650">
                    <input type="hidden" name="send_mail" value="0">
                    <input type="checkbox" name="send_mail" value="1" checked> Client ko payment email bhejo
                </label>
                <button class="kk-btn kk-btn-primary" type="submit">Save received amount</button>
            </form>

            <form method="POST" action="{{ route('admin.works.remind', $p->id) }}" style="margin-top:14px">
                @csrf
                <button class="kk-btn kk-btn-secondary" type="submit" @disabled($pending <= 0)>Send pending reminder email</button>
            </form>
        </div>
    </section>
</div>

<section class="kk-card" style="margin-top:16px">
    <div class="kk-card__body">
        <h2 style="margin:0 0 12px;font-size:16px">EMI / installments</h2>
        <form class="kk-form" method="POST" action="{{ route('admin.works.installments.store', $p->id) }}" style="margin-bottom:16px">
            @csrf
            <div class="kk-form-grid">
                <div class="kk-field">
                    <label>Label</label>
                    <input type="text" name="label" placeholder="EMI 1 / Advance">
                </div>
                <div class="kk-field">
                    <label>Amount (₹) *</label>
                    <input type="number" step="0.01" min="0.01" name="amount" required>
                </div>
                <div class="kk-field">
                    <label>Due date</label>
                    <input type="date" name="due_date">
                </div>
                <div class="kk-field">
                    <label>Notes</label>
                    <input type="text" name="notes">
                </div>
            </div>
            <label style="display:flex;gap:8px;align-items:center;font-size:13px;font-weight:650;margin:8px 0">
                <input type="hidden" name="send_mail" value="0">
                <input type="checkbox" name="send_mail" value="1" checked> EMI schedule email client ko bhejo
            </label>
            <button class="kk-btn kk-btn-secondary" type="submit">Add installment</button>
        </form>

        <div class="kk-table-wrap">
            <table class="kk-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Amount</th>
                        <th>Due</th>
                        <th>Status</th>
                        <th>Receipt</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($p->installments as $row)
                        <tr>
                            <td>{{ $row->displayLabel() }}</td>
                            <td>{{ $money($row->amount) }}</td>
                            <td>{{ $row->due_date?->format('d M Y') ?: '—' }}</td>
                            <td>
                                <span class="kk-pill kk-pill--{{ $row->status === 'paid' ? 'hired' : 'review' }}">{{ $row->status === 'paid' ? 'received' : 'pending' }}</span>
                                @if ($row->paid_at)
                                    <div class="kk-muted" style="font-size:11px;margin-top:4px">{{ \App\Support\AppTime::formatDate($row->paid_at) }}</div>
                                @endif
                            </td>
                            <td>
                                @if ($row->receiptUrl())
                                    <a href="{{ $row->receiptUrl() }}" target="_blank">{{ $row->receipt_name ?: 'View' }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                <div class="kk-actions" style="justify-content:flex-end;flex-wrap:wrap">
                                    @if ($row->status !== 'paid')
                                        <form method="POST" action="{{ route('admin.works.installments.pay', [$p->id, $row->id]) }}" enctype="multipart/form-data" style="display:flex;gap:6px;align-items:center;flex-wrap:wrap">
                                            @csrf
                                            <input type="file" name="receipt" accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/*" style="max-width:160px">
                                            <input type="hidden" name="send_mail" value="1">
                                            <button class="kk-btn kk-btn-primary kk-btn-sm" type="submit">Mark received</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.works.installments.remind', [$p->id, $row->id]) }}">
                                            @csrf
                                            <button class="kk-btn kk-btn-secondary kk-btn-sm" type="submit">Send EMI reminder</button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('admin.works.installments.destroy', [$p->id, $row->id]) }}" onsubmit="return confirm('Delete this installment?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="kk-btn kk-btn-danger kk-btn-sm" type="submit">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="kk-empty">No EMI schedule yet. Add one above.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="kk-card" style="margin-top:16px">
    <div class="kk-card__body">
        <h2 style="margin:0 0 12px;font-size:16px">Receipts</h2>
        <form class="kk-form" method="POST" action="{{ route('admin.works.receipts.store', $p->id) }}" enctype="multipart/form-data" style="margin-bottom:16px">
            @csrf
            <div class="kk-form-grid">
                <div class="kk-field">
                    <label>Upload receipt *</label>
                    <input type="file" name="receipt" required accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/*">
                </div>
                <div class="kk-field">
                    <label>Amount (optional)</label>
                    <input type="number" step="0.01" min="0" name="amount">
                </div>
                <div class="kk-field" style="grid-column:1/-1">
                    <label>Notes</label>
                    <input type="text" name="notes" maxlength="500">
                </div>
            </div>
            <button class="kk-btn kk-btn-secondary" type="submit" style="margin-top:10px">Upload receipt</button>
        </form>

        <div class="kk-table-wrap">
            <table class="kk-table">
                <thead>
                    <tr>
                        <th>File</th>
                        <th>Amount</th>
                        <th>Notes</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($p->receipts as $rec)
                        <tr>
                            <td><a href="{{ $rec->fileUrl() }}" target="_blank">{{ $rec->file_name ?: 'Open' }}</a></td>
                            <td>{{ $rec->amount !== null ? $money($rec->amount) : '—' }}</td>
                            <td>{{ $rec->notes ?: '—' }}</td>
                            <td>{{ $rec->created_at?->format('d M Y') }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.works.receipts.destroy', [$p->id, $rec->id]) }}" onsubmit="return confirm('Delete receipt?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="kk-btn kk-btn-danger kk-btn-sm" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="kk-empty">No extra receipts. EMI receipts appear in the table above.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="kk-card" style="margin-top:16px">
    <div class="kk-card__body">
        <h2 style="margin:0 0 6px;font-size:16px">Renewals (domain, server, consulting…)</h2>
        <p class="kk-muted" style="margin:0 0 14px;font-size:13px">Track domain, server, hosting, SSL, consulting, maintenance and other renewals for this project.</p>

        <form class="kk-form" method="POST" action="{{ route('admin.works.renewals.store', $p->id) }}" style="margin-bottom:16px">
            @csrf
            <div class="kk-form-grid">
                <div class="kk-field">
                    <label>Type *</label>
                    <select name="type" required>
                        @foreach (($renewalTypes ?? \App\Models\ClientProjectRenewal::TYPES) as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="kk-field">
                    <label>Name *</label>
                    <input type="text" name="name" required placeholder="e.g. example.com domain">
                </div>
                <div class="kk-field">
                    <label>Amount (₹)</label>
                    <input type="number" step="0.01" min="0" name="amount">
                </div>
                <div class="kk-field">
                    <label>Renew date</label>
                    <input type="date" name="renew_date">
                </div>
                <div class="kk-field">
                    <label>Status *</label>
                    <select name="status" required>
                        @foreach (['upcoming','due','paid','cancelled'] as $st)
                            <option value="{{ $st }}">{{ ucfirst($st) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="kk-field">
                    <label>Vendor</label>
                    <input type="text" name="vendor" placeholder="e.g. GoDaddy / AWS">
                </div>
                <div class="kk-field" style="grid-column:1/-1">
                    <label>Notes</label>
                    <input type="text" name="notes" placeholder="Optional notes">
                </div>
            </div>
            <div class="kk-form-actions" style="margin-top:8px">
                <button class="kk-btn kk-btn-primary" type="submit">Add renewal</button>
            </div>
        </form>

        <div class="kk-table-wrap">
            <table class="kk-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Name</th>
                        <th>Amount</th>
                        <th>Renew date</th>
                        <th>Status</th>
                        <th>Vendor</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($p->renewals as $ren)
                        <tr>
                            <td>{{ $ren->typeLabel() }}</td>
                            <td>
                                <strong>{{ $ren->name }}</strong>
                                @if ($ren->notes)<div class="kk-muted" style="font-size:12px">{{ $ren->notes }}</div>@endif
                            </td>
                            <td>{{ $ren->amount !== null ? $money($ren->amount) : '—' }}</td>
                            <td>{{ optional($ren->renew_date)->format('d M Y') ?: '—' }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.works.renewals.update', [$p->id, $ren->id]) }}" style="display:flex;gap:6px;align-items:center">
                                    @csrf
                                    @method('PUT')
                                    <select name="status" onchange="this.form.submit()">
                                        @foreach (['upcoming','due','paid','cancelled'] as $st)
                                            <option value="{{ $st }}" @selected($ren->status === $st)>{{ ucfirst($st) }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                            <td>{{ $ren->vendor ?: '—' }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.works.renewals.destroy', [$p->id, $ren->id]) }}" onsubmit="return confirm('Remove this renewal item?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="kk-btn kk-btn-danger kk-btn-sm" type="submit">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="kk-empty">No renewals yet. Add domain, server, consulting, maintenance or other items above.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="kk-card" style="margin-top:16px">
    <div class="kk-card__body">
        <h2 style="margin:0 0 6px;font-size:16px">Project team group</h2>
        <p class="kk-muted" style="margin:0 0 14px;font-size:13px">Add employees who work on this project. They get a Project Groups chat on the employee panel and can @mention each other.</p>

        <form class="kk-form" method="POST" action="{{ route('admin.works.members.store', $p->id) }}" style="margin-bottom:16px">
            @csrf
            <div class="kk-form-grid">
                <div class="kk-field">
                    <label>Employee *</label>
                    <select name="employee_id" required>
                        <option value="">Select employee…</option>
                        @foreach (($employees ?? collect()) as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }} — {{ $emp->email }}@if($emp->role_title) ({{ $emp->role_title }})@endif</option>
                        @endforeach
                    </select>
                </div>
                <div class="kk-field">
                    <label>Role on project (optional)</label>
                    <input type="text" name="role_label" placeholder="e.g. Developer, Designer, PM">
                </div>
            </div>
            <div class="kk-form-actions" style="margin-top:8px">
                <button class="kk-btn kk-btn-primary" type="submit">Add to group</button>
            </div>
        </form>

        <div class="kk-table-wrap">
            <table class="kk-table">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($p->members as $member)
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px">
                                    @if ($member->employee?->photoUrl())
                                        <img src="{{ $member->employee->photoUrl() }}" alt="" style="width:32px;height:32px;border-radius:8px;object-fit:cover">
                                    @endif
                                    <strong>{{ $member->employee?->name ?? '—' }}</strong>
                                </div>
                            </td>
                            <td>{{ $member->employee?->email ?? '—' }}</td>
                            <td>{{ $member->role_label ?: ($member->employee?->role_title ?: '—') }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.works.members.destroy', [$p->id, $member->id]) }}" onsubmit="return confirm('Remove this member from the project group?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="kk-btn kk-btn-danger kk-btn-sm" type="submit">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><div class="kk-empty">No team members yet. Add employees to build this project group.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
