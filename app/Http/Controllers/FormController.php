<?php

namespace App\Http\Controllers;

use App\Mail\InquiryThankYouMail;
use App\Models\ContactInquiry;
use App\Models\JobApplication;
use App\Models\JobOpening;
use App\Models\NewsletterSubscriber;
use App\Models\ProjectRequest;
use App\Support\SiteSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class FormController extends Controller
{
    public function contact(Request $request): RedirectResponse
    {
        if (! SiteSettings::bool('feature_contact_form')) {
            return back()->withErrors(['form' => 'Contact form is currently disabled.']);
        }
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180'],
            'phone' => ['nullable', 'string', 'max:40'],
            'company' => ['nullable', 'string', 'max:160'],
            'service' => ['nullable', 'string', 'max:120'],
            'message' => ['required', 'string', 'max:5000'],
            'agreed_terms' => ['accepted'],
        ]);

        ContactInquiry::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'company' => $data['company'] ?? null,
            'service' => $data['service'] ?? null,
            'message' => $data['message'],
            'agreed_terms' => true,
            'status' => 'new',
        ]);

        $this->sendThankYouMail('contact', $data['name'], $data['email'], $data['service'] ?? null, $data['message']);

        return back()->with('success', 'Thanks! Your message has been received. A confirmation email has been sent to you.');
    }

    public function project(Request $request): RedirectResponse
    {
        if (! SiteSettings::bool('feature_project_form')) {
            return back()->withErrors(['form' => 'Project form is currently disabled.']);
        }
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180'],
            'phone' => ['nullable', 'string', 'max:40'],
            'company' => ['nullable', 'string', 'max:160'],
            'service' => ['nullable', 'string', 'max:120'],
            'budget_range' => ['nullable', 'string', 'max:80'],
            'timeline' => ['nullable', 'string', 'max:80'],
            'description' => ['required', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'max:5120'],
        ]);

        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('project-attachments', 'public');
        }

        ProjectRequest::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'company' => $data['company'] ?? null,
            'service' => $data['service'] ?? null,
            'budget_range' => $data['budget_range'] ?? null,
            'timeline' => $data['timeline'] ?? null,
            'description' => $data['description'],
            'attachment_path' => $path,
            'status' => 'new',
        ]);

        $this->sendThankYouMail('quote', $data['name'], $data['email'], $data['service'] ?? null, $data['description']);

        return back()->with('success', 'Project request received! A thank-you email has been sent. Our team will contact you soon.');
    }

    public function jobApply(Request $request): RedirectResponse
    {
        if (! SiteSettings::bool('feature_careers')) {
            return back()->withErrors(['form' => 'Job applications are currently closed.']);
        }
        $data = $request->validate([
            'job_opening_id' => ['nullable', 'integer', 'exists:job_openings,id'],
            'position' => ['required', 'string', 'max:180'],
            'department' => ['required', 'string', 'max:120'],
            'job_type' => ['required', 'string', 'max:80'],
            'full_name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:180'],
            'phone' => ['required', 'string', 'max:40'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:40'],
            'nationality' => ['nullable', 'string', 'max:80'],
            'address' => ['nullable', 'string', 'max:2000'],
            'qualification' => ['required', 'string', 'max:120'],
            'university' => ['nullable', 'string', 'max:180'],
            'passing_year' => ['nullable', 'string', 'max:20'],
            'field_of_study' => ['nullable', 'string', 'max:120'],
            'percentage_cgpa' => ['nullable', 'string', 'max:40'],
            'total_experience' => ['nullable', 'string', 'max:80'],
            'last_company' => ['nullable', 'string', 'max:180'],
            'job_title' => ['nullable', 'string', 'max:160'],
            'responsibilities' => ['nullable', 'string', 'max:5000'],
            'resume' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'cover_letter' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'why_join' => ['nullable', 'string', 'max:5000'],
            'source' => ['nullable', 'string', 'max:120'],
            'declared' => ['accepted'],
        ]);

        if (! empty($data['job_opening_id'])) {
            $job = JobOpening::query()->find($data['job_opening_id']);
            if ($job) {
                $data['position'] = $job->title;
            }
        }

        $resumePath = $request->file('resume')->store('job-applications/resumes', 'public');
        $coverPath = $request->hasFile('cover_letter')
            ? $request->file('cover_letter')->store('job-applications/cover-letters', 'public')
            : null;

        JobApplication::query()->create([
            'job_opening_id' => $data['job_opening_id'] ?? null,
            'position' => $data['position'],
            'department' => $data['department'],
            'job_type' => $data['job_type'],
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'gender' => $data['gender'] ?? null,
            'nationality' => $data['nationality'] ?? null,
            'address' => $data['address'] ?? null,
            'qualification' => $data['qualification'],
            'university' => $data['university'] ?? null,
            'passing_year' => $data['passing_year'] ?? null,
            'field_of_study' => $data['field_of_study'] ?? null,
            'percentage_cgpa' => $data['percentage_cgpa'] ?? null,
            'total_experience' => $data['total_experience'] ?? null,
            'last_company' => $data['last_company'] ?? null,
            'job_title' => $data['job_title'] ?? null,
            'responsibilities' => $data['responsibilities'] ?? null,
            'resume_path' => $resumePath,
            'cover_letter_path' => $coverPath,
            'why_join' => $data['why_join'] ?? null,
            'source' => $data['source'] ?? 'Career Website',
            'declared' => true,
            'status' => 'new',
        ]);

        return redirect()
            ->route('careers.apply', array_filter(['job' => $data['job_opening_id'] ?? null]))
            ->with('success', 'Application submitted successfully! Our HR team will review and contact you soon.');
    }

    public function newsletter(Request $request): RedirectResponse
    {
        if (! SiteSettings::bool('feature_newsletter', false)) {
            return back()->withErrors(['email' => 'Newsletter signup is currently disabled.']);
        }
        $data = $request->validate([
            'email' => ['required', 'email', 'max:180'],
        ]);

        NewsletterSubscriber::query()->updateOrCreate(
            ['email' => $data['email']],
            ['is_active' => true]
        );

        return back()->with('success', 'You’re subscribed to our newsletter.');
    }

    private function sendThankYouMail(string $type, string $name, string $email, ?string $service, ?string $summary): void
    {
        try {
            Mail::to($email)->send(new InquiryThankYouMail($type, $name, $email, $service, $summary));
        } catch (Throwable $e) {
            Log::error('Thank-you mail failed', [
                'type' => $type,
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
