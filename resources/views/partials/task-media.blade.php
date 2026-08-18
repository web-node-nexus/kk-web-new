@php
    $pdfUrl = \App\Support\TaskMedia::url($item->pdf_path ?? null);
    $videoUrl = \App\Support\TaskMedia::url($item->video_path ?? null);
    $fileUrl = \App\Support\TaskMedia::url($item->file_path ?? null);
    $hasMedia = \App\Support\TaskMedia::hasAny($item);
@endphp
@if ($hasMedia)
    <div class="task-attach">
        @if ($pdfUrl)
            <a class="task-attach__chip" href="{{ $pdfUrl }}" target="_blank" rel="noopener">PDF · {{ $item->pdf_name ?: 'Open PDF' }}</a>
        @endif
        @if ($videoUrl)
            <a class="task-attach__chip" href="{{ $videoUrl }}" target="_blank" rel="noopener">Video · {{ $item->video_name ?: 'Open video' }}</a>
        @endif
        @if ($fileUrl)
            <a class="task-attach__chip" href="{{ $fileUrl }}" target="_blank" rel="noopener">File · {{ $item->file_name ?: 'Download' }}</a>
        @endif
        @if ($item->link_url)
            <a class="task-attach__chip" href="{{ $item->link_url }}" target="_blank" rel="noopener">Link</a>
        @endif
        @if ($item->meet_link)
            <a class="task-attach__chip is-meet" href="{{ $item->meet_link }}" target="_blank" rel="noopener">Join Meet</a>
        @endif
    </div>
    @if ($videoUrl)
        <video class="task-attach__video" src="{{ $videoUrl }}" controls preload="metadata"></video>
    @endif
@endif
