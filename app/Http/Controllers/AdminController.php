<?php

namespace App\Http\Controllers;

use App\Models\ContactInquiry;
use App\Models\JobApplication;
use App\Models\JobOpening;
use App\Models\ProjectRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        return view('admin.dashboard', [
            'user' => Auth::user(),
            'stats' => [
                'applications' => JobApplication::query()->count(),
                'new_applications' => JobApplication::query()->where('status', 'new')->count(),
                'open_jobs' => JobOpening::query()->where('is_open', true)->count(),
                'contacts' => ContactInquiry::query()->count(),
                'projects' => ProjectRequest::query()->count(),
            ],
            'recentApplications' => JobApplication::query()->latest()->take(5)->get(),
        ]);
    }

    public function applications(Request $request): View
    {
        $query = JobApplication::query()->with('jobOpening')->latest();

        if ($request->filled('status') && $request->string('status') !== 'all') {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('q')) {
            $term = '%'.$request->string('q').'%';
            $query->where(function ($q) use ($term) {
                $q->where('full_name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('position', 'like', $term)
                    ->orWhere('phone', 'like', $term);
            });
        }

        return view('admin.applications', [
            'user' => Auth::user(),
            'applications' => $query->paginate(12)->withQueryString(),
            'filters' => [
                'status' => $request->string('status')->toString() ?: 'all',
                'q' => $request->string('q')->toString(),
            ],
            'counts' => [
                'all' => JobApplication::query()->count(),
                'new' => JobApplication::query()->where('status', 'new')->count(),
                'reviewed' => JobApplication::query()->where('status', 'reviewed')->count(),
                'shortlisted' => JobApplication::query()->where('status', 'shortlisted')->count(),
                'rejected' => JobApplication::query()->where('status', 'rejected')->count(),
            ],
        ]);
    }

    public function applicationShow(JobApplication $application): View
    {
        $application->load('jobOpening');

        return view('admin.application-show', [
            'user' => Auth::user(),
            'application' => $application,
        ]);
    }

    public function applicationStatus(Request $request, JobApplication $application): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:new,reviewed,shortlisted,rejected'],
        ]);

        $application->update(['status' => $data['status']]);

        return back()->with('success', 'Application status updated to '.$data['status'].'.');
    }
}
