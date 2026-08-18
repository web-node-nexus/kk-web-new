<div class="ep-field ep-field--full">
    <label>Attachments & links</label>
    <p class="ep-muted" style="margin:4px 0 10px;font-size:12px">Revert ke saath PDF, video, file, link ya Meet link bhej sakte ho.</p>
    <div class="ep-media-grid">
        <div class="ep-field">
            <label for="pdf">PDF</label>
            <input id="pdf" type="file" name="pdf" accept="application/pdf,.pdf">
        </div>
        <div class="ep-field">
            <label for="video">Video</label>
            <input id="video" type="file" name="video" accept="video/mp4,video/webm,video/quicktime,.mp4,.webm,.mov">
        </div>
        <div class="ep-field">
            <label for="file">Choose file</label>
            <input id="file" type="file" name="file">
        </div>
        <div class="ep-field">
            <label for="link_url">Link</label>
            <input id="link_url" type="url" name="link_url" value="{{ old('link_url') }}" placeholder="https://…">
        </div>
        <div class="ep-field ep-field--full">
            <label for="meet_link">Meet link</label>
            <input id="meet_link" type="url" name="meet_link" value="{{ old('meet_link') }}" placeholder="https://meet.google.com/…">
        </div>
    </div>
</div>
