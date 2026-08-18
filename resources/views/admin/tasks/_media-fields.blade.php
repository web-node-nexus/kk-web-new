@php
    $prefix = $fieldPrefix ?? '';
@endphp
<div class="kk-field" style="grid-column:1/-1">
    <label>Attachments & links</label>
    <p class="kk-muted" style="margin:4px 0 10px;font-size:12px">PDF, video, any file, resource link, aur Meet link — sab optional.</p>
    <div class="kk-media-grid">
        <div class="kk-field">
            <label>PDF</label>
            <input type="file" name="{{ $prefix }}pdf" accept="application/pdf,.pdf">
            <span class="kk-muted" style="font-size:11px">Max 15 MB</span>
        </div>
        <div class="kk-field">
            <label>Video</label>
            <input type="file" name="{{ $prefix }}video" accept="video/mp4,video/webm,video/quicktime,.mp4,.webm,.mov,.avi">
            <span class="kk-muted" style="font-size:11px">Max 50 MB</span>
        </div>
        <div class="kk-field">
            <label>Choose file</label>
            <input type="file" name="{{ $prefix }}file">
            <span class="kk-muted" style="font-size:11px">Any file, max 20 MB</span>
        </div>
        <div class="kk-field">
            <label>Link</label>
            <input type="url" name="{{ $prefix }}link_url" value="{{ old($prefix.'link_url') }}" placeholder="https://…">
        </div>
        <div class="kk-field" style="grid-column:1/-1">
            <label>Meet link</label>
            <input type="url" name="{{ $prefix }}meet_link" value="{{ old($prefix.'meet_link') }}" placeholder="https://meet.google.com/…  or Zoom / Teams">
        </div>
    </div>
</div>
