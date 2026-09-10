@extends('layouts.admin')

@section('title', ($mode === 'create' ? 'Add' : 'Edit').' '.$mod['title'].' | Admin')

@section('content')
<div class="kk-pagehead">
    <div>
        <h1>{{ $mode === 'create' ? 'Add' : 'Edit' }} {{ \Illuminate\Support\Str::singular($mod['title']) }}</h1>
        <p>{{ $mod['subtitle'] ?? '' }}</p>
    </div>
    <div class="kk-pagehead__actions">
        <a href="{{ route('admin.module.index', $mod['key']) }}" class="kk-btn kk-btn-secondary">Back to list</a>
    </div>
</div>

<section class="kk-card">
    <div class="kk-card__body">
        <form class="kk-form" method="POST" action="{{ $mode === 'create' ? route('admin.module.store', $mod['key']) : route('admin.module.update', [$mod['key'], $item->id]) }}" @if(collect($mod['fields'])->contains(fn ($f) => ($f['type'] ?? '') === 'file')) enctype="multipart/form-data" @endif>
            @csrf
            @if ($mode === 'edit')
                @method('PUT')
            @endif

            <div class="kk-form-grid">
                @foreach ($mod['fields'] as $field)
                    @continue(!empty($field['show_only']))
                    @php
                        $name = $field['name'];
                        $type = $field['type'] ?? 'text';
                        $value = old($name, $item->{$name} ?? ($field['default'] ?? ''));
                        if ($name === 'department_name') {
                            $value = old($name, $item?->department?->name ?? ($field['default'] ?? ''));
                        }
                        if ($type === 'password' || $name === 'panel_password') {
                            $value = old($name, '');
                        }
                        if ($type === 'datetime-local' && $value) {
                            try { $value = \Illuminate\Support\Carbon::parse($value)->format('Y-m-d\TH:i'); } catch (\Throwable) {}
                        }
                        if ($type === 'date' && $value) {
                            try { $value = \Illuminate\Support\Carbon::parse($value)->format('Y-m-d'); } catch (\Throwable) {}
                        }
                        $checkedDefault = $mode === 'create'
                            ? array_key_exists('default', $field) ? (bool) $field['default'] : false
                            : (bool) old($name, $item->{$name} ?? false);
                        $fieldRequired = !empty($field['required']) || (!empty($field['required_on_create']) && $mode === 'create');
                    @endphp
                    <div class="kk-field" style="{{ !empty($field['full']) ? 'grid-column:1/-1' : '' }}">
                        <label for="f_{{ $name }}">{{ $field['label'] }}@if($fieldRequired) * @endif</label>

                        @if ($type === 'textarea')
                            <textarea id="f_{{ $name }}" name="{{ $name }}" rows="5">{{ $value }}</textarea>
                        @elseif ($type === 'file')
                            @if ($mode === 'edit' && $name === 'photo_path' && $item?->photoUrl())
                                <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px">
                                    <img src="{{ $item->photoUrl() }}" alt="" style="width:56px;height:56px;object-fit:cover;border-radius:12px;border:1px solid #e2e8f0">
                                    <span class="kk-muted" style="font-size:12px">Current photo</span>
                                </div>
                            @endif
                            <input id="f_{{ $name }}" type="file" name="{{ $name }}" accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp">
                        @elseif ($type === 'select')
                            <select id="f_{{ $name }}" name="{{ $name }}">
                                @php $opts = $field['options'] ?? []; if ($value && !in_array((string)$value, array_map('strval', $opts), true)) { $opts = array_merge([(string)$value], $opts); } @endphp
                                @foreach ($opts as $opt)
                                    <option value="{{ $opt }}" @selected((string)$value === (string)$opt)>{{ $opt }}</option>
                                @endforeach
                            </select>
                        @elseif ($type === 'model_select')
                            @php
                                $opts = ($field['model'] ?? null) ? $field['model']::query()->orderBy($field['option_label'] ?? 'name')->get() : collect();
                                $labelKey = $field['option_label'] ?? 'name';
                            @endphp
                            <select id="f_{{ $name }}" name="{{ $name }}">
                                <option value="">Select…</option>
                                @foreach ($opts as $opt)
                                    <option value="{{ $opt->id }}" @selected((string)$value === (string)$opt->id)>{{ $opt->{$labelKey} }}</option>
                                @endforeach
                            </select>
                        @elseif ($type === 'checkbox')
                            <label style="display:flex;align-items:center;gap:8px;font-weight:500">
                                <input type="hidden" name="{{ $name }}" value="0">
                                <input type="checkbox" id="f_{{ $name }}" name="{{ $name }}" value="1" @checked(old($name, $checkedDefault))>
                                Yes — show on website
                            </label>
                        @elseif ($type === 'hr_picker')
                            <div style="position:relative">
                                @include('admin.partials.hr-picker', [
                                    'inputName' => $name,
                                    'selectedEmail' => $value,
                                    'required' => $fieldRequired,
                                    'pickerId' => 'hrPickerForm_'.$name,
                                ])
                            </div>
                        @elseif ($type === 'password' || $name === 'panel_password')
                            <input
                                id="f_{{ $name }}"
                                type="text"
                                name="{{ $name }}"
                                value="{{ $value }}"
                                autocomplete="off"
                                autocorrect="off"
                                autocapitalize="off"
                                spellcheck="false"
                                @if($fieldRequired) required @endif
                                minlength="6"
                                placeholder="{{ $mode === 'edit' ? 'Blank = keep old password' : 'Type panel password here' }}"
                                style="letter-spacing:.02em"
                            >
                        @elseif ($name === 'employee_code')
                            <div style="display:flex;gap:8px;align-items:center">
                                <input id="f_{{ $name }}" type="text" name="{{ $name }}" value="{{ $value }}" placeholder="Leave blank to auto-generate" style="flex:1">
                                <button type="button" class="kk-btn kk-btn-secondary" id="genEmpCodeBtn">Generate</button>
                            </div>
                        @else
                            <input id="f_{{ $name }}" type="{{ $type === 'number' ? 'number' : ($type === 'email' ? 'email' : ($type === 'time' ? 'time' : ($type === 'date' ? 'date' : ($type === 'datetime-local' ? 'datetime-local' : 'text')))) }}" name="{{ $name }}" value="{{ $value }}" @if(($type ?? '') === 'email') autocomplete="off" @endif>
                        @endif
                        @if ($type === 'file' && $mode === 'edit' && $name === 'photo' && !empty($item->photo))
                            <div style="margin-top:8px">
                                <img src="{{ asset($item->photo) }}" alt="" style="width:56px;height:56px;object-fit:cover;border-radius:12px;border:1px solid #e2e8f0">
                            </div>
                        @endif
                        @if (!empty($field['hint']))
                            <p style="margin:6px 0 0;font-size:12px;color:#64748b">{{ $field['hint'] }}</p>
                        @endif
                        @error($name)<p style="margin:6px 0 0;font-size:12px;color:#b91c1c">{{ $message }}</p>@enderror
                    </div>
                @endforeach
            </div>

            <div class="kk-form-actions">
                <a href="{{ route('admin.module.index', $mod['key']) }}" class="kk-btn kk-btn-secondary">Cancel</a>
                <button class="kk-btn kk-btn-primary" type="submit">{{ $mode === 'create' ? 'Create' : 'Save changes' }}</button>
            </div>
        </form>
    </div>
</section>
@endsection

@if (($mod['key'] ?? '') === 'employees')
@push('scripts')
<script>
(() => {
  const btn = document.getElementById('genEmpCodeBtn');
  const input = document.getElementById('f_employee_code');
  if (!btn || !input) return;
  btn.addEventListener('click', async () => {
    try {
      const token = document.querySelector('meta[name="csrf-token"]')?.content
        || document.querySelector('input[name="_token"]')?.value;
      const res = await fetch(@json(route('admin.employees.generate-code')), {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': token || '',
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({}),
      });
      const data = await res.json();
      if (data.employee_code) {
        input.value = data.employee_code;
      } else if (window.kkToast) {
        window.kkToast(data.message || 'Could not generate ID');
      } else {
        alert(data.message || 'Could not generate ID');
      }
    } catch (e) {
      alert('Could not generate employee ID');
    }
  });
})();
</script>
@endpush
@endif

@if(collect($mod['fields'] ?? [])->contains(fn ($f) => ($f['type'] ?? '') === 'hr_picker'))
@push('scripts')
@include('admin.partials.hr-picker-script')
@endpush
@endif
