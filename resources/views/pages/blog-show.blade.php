@extends('layouts.app')
@section('title', $post->title.' | TechNex')
@section('description', $post->excerpt)
@section('content')
@include('partials.page-hero', [
    'title' => $post->title,
    'subtitle' => optional($post->published_at)->format('F j, Y').' · '.$post->read_time_mins.' min read · '.($post->category?->name ?? 'Insights'),
    'crumbs' => [
        ['label' => 'Home', 'href' => route('home')],
        ['label' => 'Insights', 'href' => route('blog')],
        ['label' => 'Article'],
    ],
])
<section class="tn-section">
    <div class="container-tn max-w-3xl">
        @if ($post->cover_image)
            <img src="{{ $post->cover_image }}" alt="" class="mb-8 w-full rounded-2xl object-cover shadow-md">
        @endif
        <div class="space-y-5 text-[1.05rem] leading-relaxed text-[#6b7280]">
            {!! $post->body !!}
        </div>
        @if ($related->isNotEmpty())
            <div class="mt-14 border-t border-slate-100 pt-10">
                <h2 class="text-2xl font-bold">Related insights</h2>
                <div class="mt-5 grid gap-4 sm:grid-cols-3">
                    @foreach ($related as $item)
                        <a href="{{ route('blog.show', $item->slug) }}" class="tn-card p-5 text-sm font-semibold hover:text-[#2f80ed]">{{ $item->title }}</a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>
@endsection
