@extends('layouts.admin')
@section('title', 'Backup & Restore | Admin')
@section('content')
<div class="kk-pagehead">
    <div>
        <h1>Backup & Restore</h1>
        <p>Manage database and media backups for disaster recovery.</p>
    </div>
    <div class="kk-pagehead__actions">
        <button class="kk-btn kk-btn-primary" type="button" onclick="alert('Backup queued successfully.')">Create backup</button>
    </div>
</div>
<section class="kk-card">
    <div class="kk-table-wrap">
        <table class="kk-table">
            <thead><tr><th>File</th><th>Type</th><th>Size</th><th>Created</th><th></th></tr></thead>
            <tbody>
                @foreach ($backups as $b)
                    <tr>
                        <td class="kk-row-title">{{ $b['file'] }}</td>
                        <td>{{ $b['type'] }}</td>
                        <td>{{ $b['size'] }}</td>
                        <td class="kk-muted">{{ $b['date'] }}</td>
                        <td><button class="kk-btn kk-btn-secondary kk-btn-sm" type="button">Download</button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
@endsection
