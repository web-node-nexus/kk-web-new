<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\CaseStudy;
use App\Models\Department;
use App\Models\Faq;
use App\Models\JobOpening;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        return view('pages.home', [
            'services' => Service::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'cases' => CaseStudy::query()->orderBy('sort_order')->take(6)->get(),
            'stats' => SiteSetting::get('home_stats', []),
            'trusted' => SiteSetting::get('trusted_logos', []),
        ]);
    }

    public function whyUs(): View
    {
        return view('pages.why-us', [
            'team' => TeamMember::query()->orderBy('sort_order')->get(),
            'trusted' => SiteSetting::get('trusted_logos', []),
        ]);
    }

    public function about(): View
    {
        return view('pages.about', [
            'founders' => TeamMember::query()->where('group', 'founder')->orderBy('sort_order')->get(),
            'team' => TeamMember::query()->where('group', 'team')->orderBy('sort_order')->get(),
        ]);
    }

    public function caseStudies(Request $request): View
    {
        $folders = [
            [
                'key' => 'website',
                'label' => 'Website Application',
                'hint' => 'Live websites & web apps',
                'icon' => 'images/icons/folder-website.png',
            ],
            [
                'key' => 'mobile',
                'label' => 'Mobile Application',
                'hint' => 'iOS & Android apps',
                'icon' => 'images/icons/folder-mobile.png',
            ],
            [
                'key' => 'software',
                'label' => 'Software',
                'hint' => 'Custom software products',
                'icon' => 'images/icons/folder-software.png',
            ],
        ];

        $activeFolder = $request->string('folder')->toString();
        $validKeys = collect($folders)->pluck('key');

        if (! $validKeys->contains($activeFolder)) {
            $activeFolder = '';
        }

        $cases = $activeFolder
            ? CaseStudy::query()->where('folder', $activeFolder)->orderBy('sort_order')->get()
            : collect();

        $folderCounts = CaseStudy::query()
            ->selectRaw('folder, COUNT(*) as total')
            ->groupBy('folder')
            ->pluck('total', 'folder');

        return view('pages.case-studies', [
            'folders' => $folders,
            'activeFolder' => $activeFolder,
            'cases' => $cases,
            'folderCounts' => $folderCounts,
        ]);
    }

    public function blog(Request $request): View
    {
        $query = BlogPost::query()->with('category')->whereNotNull('published_at')->latest('published_at');

        if ($request->filled('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->string('category')));
        }

        if ($request->filled('q')) {
            $term = '%'.$request->string('q').'%';
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', $term)->orWhere('excerpt', 'like', $term);
            });
        }

        return view('pages.blog', [
            'posts' => $query->paginate(6)->withQueryString(),
            'categories' => BlogCategory::query()->orderBy('name')->get(),
            'popular' => BlogPost::query()->where('is_popular', true)->latest('published_at')->take(3)->get(),
        ]);
    }

    public function blogShow(string $slug): View
    {
        $post = BlogPost::query()->with('category')->where('slug', $slug)->firstOrFail();

        return view('pages.blog-show', [
            'post' => $post,
            'related' => BlogPost::query()
                ->where('id', '!=', $post->id)
                ->where('blog_category_id', $post->blog_category_id)
                ->latest('published_at')
                ->take(3)
                ->get(),
        ]);
    }

    public function careers(): View
    {
        return view('pages.careers', [
            'jobs' => JobOpening::query()->where('is_open', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function careersApply(Request $request): View
    {
        $jobs = JobOpening::query()->where('is_open', true)->orderBy('sort_order')->get();
        $selectedJob = null;

        if ($request->filled('job')) {
            $selectedJob = JobOpening::query()->where('is_open', true)->find($request->integer('job'));
        }

        $departments = Department::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('pages.careers-apply', [
            'jobs' => $jobs,
            'selectedJob' => $selectedJob,
            'departments' => $departments,
        ]);
    }

    public function contact(): View
    {
        return view('pages.contact', [
            'services' => Service::query()->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function startProject(): View
    {
        return view('pages.start-project', [
            'services' => Service::query()->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function faq(): View
    {
        return view('pages.faq', [
            'faqs' => Faq::query()->orderBy('sort_order')->get(),
        ]);
    }

    public function privacy(): View
    {
        return view('pages.privacy');
    }

    public function terms(): View
    {
        return view('pages.terms');
    }
}
