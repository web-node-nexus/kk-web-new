@extends('layouts.app')
@section('title', 'Insights | TechNex')
@section('content')
@include('partials.page-hero', [
    'title' => 'Insights',
    'subtitle' => 'Perspectives on digital transformation, engineering excellence, cloud, security and AI.',
    'crumbs' => [['label' => 'Home', 'href' => route('home')], ['label' => 'Insights']],
])

<section class="tn-section">
    <div class="container-tn grid gap-10 lg:grid-cols-[1fr_300px]">
        <div>
            <form method="GET" class="mb-8 flex flex-col gap-3 sm:flex-row">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search insights..." class="form-input sm:max-w-xs">
                <select name="category" class="form-input sm:max-w-[220px]">
                    <option value="">All categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <button class="tn-btn tn-btn-primary" type="submit">Filter</button>
            </form>

            <div class="tn-feature-grid">
                @forelse ($posts as $post)
                    <a href="{{ route('blog.show', $post->slug) }}" class="tn-card overflow-hidden transition hover:-translate-y-1">
                        @if ($post->cover_image)
                            <img src="{{ $post->cover_image }}" alt="" class="h-44 w-full object-cover">
                        @endif
                        <div class="p-6">
                            <p class="m-0 text-xs font-bold uppercase tracking-wider text-[#2f80ed]">{{ $post->category?->name }}</p>
                            <h2 class="mt-2 text-lg font-bold">{{ $post->title }}</h2>
                            <p class="mt-2 text-sm text-[#6b7280] line-clamp-3">{{ $post->excerpt }}</p>
                            <p class="mt-4 text-xs font-semibold text-[#94a3b8]">{{ optional($post->published_at)->format('M j, Y') }} · {{ $post->read_time_mins }} min</p>
                        </div>
                    </a>
                @empty
                    <p class="text-[#6b7280]">No posts found.</p>
                @endforelse
            </div>
            <div class="mt-8">{{ $posts->links() }}</div>
        </div>

        <aside class="space-y-5">
            <div class="tn-card p-6">
                <h3 class="m-0 text-lg font-bold">Popular</h3>
                <div class="mt-4 space-y-3">
                    @foreach ($popular as $item)
                        <a href="{{ route('blog.show', $item->slug) }}" class="block text-sm font-semibold leading-snug hover:text-[#2f80ed]">{{ $item->title }}</a>
                    @endforeach
                </div>
            </div>
            <div class="tn-card p-6">
                <h3 class="m-0 text-lg font-bold">Newsletter</h3>
                <p class="mt-2 text-sm text-[#6b7280]">Technology briefs for business leaders.</p>
                <form method="POST" action="{{ route('newsletter.submit') }}" class="mt-4 space-y-3">
                    @csrf
                    <input type="email" name="email" required placeholder="you@company.com" class="form-input">
                    <button class="tn-btn tn-btn-primary w-full !py-2.5 text-sm" type="submit">Subscribe</button>
                </form>
            </div>
        </aside>
    </div>
</section>
@endsection
