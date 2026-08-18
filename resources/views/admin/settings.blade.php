@extends('layouts.admin')
@section('title', 'Settings | Admin')

@section('content')
<div class="kk-pagehead">
    <div>
        <h1>Settings</h1>
        <p>Company details + features ON/OFF — sab yahi se control karo.</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.settings.update') }}">
    @csrf
    @method('PUT')

    @foreach ($schema as $groupKey => $group)
        <section class="kk-card" style="margin-bottom:16px">
            <div class="kk-card__body">
                <h2 style="margin:0 0 14px;font-size:16px">{{ $group['label'] }}</h2>

                @if (($group['fields'][0]['type'] ?? '') === 'toggle')
                    <div style="display:grid;gap:10px">
                        @foreach ($group['fields'] as $field)
                            @php $on = in_array((string)($values[$field['key']] ?? '1'), ['1','true','on','yes'], true); @endphp
                            <label style="display:flex;align-items:center;justify-content:space-between;gap:16px;padding:12px 14px;border:1px solid #e2e8f0;border-radius:12px;background:#fff;cursor:pointer">
                                <span>
                                    <strong style="display:block;font-size:14px">{{ $field['label'] }}</strong>
                                    @if (!empty($field['hint']))
                                        <span class="kk-muted" style="font-size:12px">{{ $field['hint'] }}</span>
                                    @endif
                                </span>
                                <span style="display:flex;align-items:center;gap:10px">
                                    <em class="kk-muted" style="font-style:normal;font-size:12px;font-weight:700;min-width:28px">{{ $on ? 'ON' : 'OFF' }}</em>
                                    <input type="checkbox" name="{{ $field['key'] }}" value="1" @checked($on) style="width:18px;height:18px">
                                </span>
                            </label>
                        @endforeach
                    </div>
                @else
                    <div class="kk-form-grid">
                        @foreach ($group['fields'] as $field)
                            <div class="kk-field" style="{{ in_array($field['key'], ['company_address','support_phone'], true) ? 'grid-column:1/-1' : '' }}">
                                <label>{{ $field['label'] }}</label>
                                <input
                                    type="{{ $field['type'] === 'email' ? 'email' : 'text' }}"
                                    name="{{ $field['key'] }}"
                                    value="{{ old($field['key'], $values[$field['key']] ?? '') }}"
                                    @if(in_array($field['key'], ['company_name','support_email'], true)) required @endif
                                >
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    @endforeach

    <div class="kk-form-actions" style="margin-bottom:24px">
        <button class="kk-btn kk-btn-primary" type="submit">Save all settings</button>
    </div>
</form>
@endsection
