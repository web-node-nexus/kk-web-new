@extends('layouts.app')
@section('title', 'Apply Now | KK Digital Solution')
@section('content')
@include('partials.page-hero', [
    'title' => 'Build Your Career With Us',
    'subtitle' => 'Join our team and be a part of an innovative company that values creativity, growth and a positive impact.',
    'crumbs' => [
        ['label' => 'Home', 'href' => route('home')],
        ['label' => 'Careers', 'href' => route('careers')],
        ['label' => 'Apply Now'],
    ],
])

@php
    $selectedPosition = old('position', $selectedJob?->title);
    $selectedDepartment = old('department', $selectedJob?->department_name);
    $selectedJobType = old('job_type', $selectedJob?->employment_type);
    $years = range((int) date('Y'), 1995);
@endphp

<section class="tn-section tn-apply">
    <div class="container-tn tn-apply__grid">
        <form
            method="POST"
            action="{{ route('careers.apply.submit') }}"
            enctype="multipart/form-data"
            class="tn-apply__form tn-card"
            id="job-apply-form"
        >
            @csrf
            @if ($selectedJob)
                <input type="hidden" name="job_opening_id" value="{{ $selectedJob->id }}">
            @endif

            <div class="tn-apply__section">
                <div class="tn-apply__section-head">
                    <span class="tn-apply__num">1</span>
                    <div>
                        <h2>Position Details</h2>
                        <p>Tell us which role you are applying for.</p>
                    </div>
                </div>
                <div class="tn-apply__fields">
                    <div>
                        <label>Position Applied For <span>*</span></label>
                        <select name="position" class="form-input" required>
                            <option value="">Select position</option>
                            @foreach ($jobs as $job)
                                <option value="{{ $job->title }}" @selected($selectedPosition === $job->title)>{{ $job->title }}</option>
                            @endforeach
                            <option value="Internship" @selected($selectedPosition === 'Internship')>Internship</option>
                            <option value="Other" @selected($selectedPosition === 'Other')>Other</option>
                        </select>
                        @error('position')<p class="tn-apply__error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label>Department <span>*</span></label>
                        <select name="department" class="form-input" required>
                            <option value="">Select department</option>
                            @forelse ($departments as $dept)
                                <option value="{{ $dept->name }}" @selected($selectedDepartment === $dept->name)>{{ $dept->name }}</option>
                            @empty
                                <option value="General" @selected($selectedDepartment === 'General')>General</option>
                            @endforelse
                        </select>
                        @error('department')<p class="tn-apply__error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label>Job Type <span>*</span></label>
                        <select name="job_type" class="form-input" required>
                            <option value="">Select job type</option>
                            @foreach (['Full-time', 'Part-time', 'Internship', 'Contract', 'Remote'] as $type)
                                <option value="{{ $type }}" @selected($selectedJobType === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                        @error('job_type')<p class="tn-apply__error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="tn-apply__section">
                <div class="tn-apply__section-head">
                    <span class="tn-apply__num">2</span>
                    <div>
                        <h2>Personal Information</h2>
                        <p>Basic details so we can reach you.</p>
                    </div>
                </div>
                <div class="tn-apply__fields tn-apply__fields--2">
                    <div>
                        <label>Full Name <span>*</span></label>
                        <input type="text" name="full_name" value="{{ old('full_name') }}" class="form-input" required placeholder="Your full name">
                        @error('full_name')<p class="tn-apply__error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label>Email Address <span>*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-input" required placeholder="you@email.com">
                        @error('email')<p class="tn-apply__error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label>Phone Number <span>*</span></label>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="form-input" required placeholder="+91 ...">
                        @error('phone')<p class="tn-apply__error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label>Date of Birth</label>
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" class="form-input">
                    </div>
                    <div>
                        <label>Gender</label>
                        <select name="gender" class="form-input">
                            <option value="">Select gender</option>
                            @foreach (['Male', 'Female', 'Other', 'Prefer not to say'] as $g)
                                <option value="{{ $g }}" @selected(old('gender') === $g)>{{ $g }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label>Nationality</label>
                        <select name="nationality" class="form-input">
                            <option value="">Select nationality</option>
                            @foreach (['Indian', 'Other'] as $n)
                                <option value="{{ $n }}" @selected(old('nationality') === $n)>{{ $n }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="tn-apply__full">
                        <label>Current Address</label>
                        <textarea name="address" rows="3" class="form-input" placeholder="House / Street, City, State, PIN">{{ old('address') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="tn-apply__section">
                <div class="tn-apply__section-head">
                    <span class="tn-apply__num">3</span>
                    <div>
                        <h2>Education Details</h2>
                        <p>Share your academic background.</p>
                    </div>
                </div>
                <div class="tn-apply__fields tn-apply__fields--2">
                    <div>
                        <label>Highest Qualification <span>*</span></label>
                        <select name="qualification" class="form-input" required>
                            <option value="">Select qualification</option>
                            @foreach (['High School', 'Diploma', 'Bachelor\'s Degree', 'Master\'s Degree', 'PhD', 'Other'] as $q)
                                <option value="{{ $q }}" @selected(old('qualification') === $q)>{{ $q }}</option>
                            @endforeach
                        </select>
                        @error('qualification')<p class="tn-apply__error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label>University / College</label>
                        <input type="text" name="university" value="{{ old('university') }}" class="form-input" placeholder="Institution name">
                    </div>
                    <div>
                        <label>Passing Year</label>
                        <select name="passing_year" class="form-input">
                            <option value="">Select year</option>
                            @foreach ($years as $year)
                                <option value="{{ $year }}" @selected(old('passing_year') == $year)>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label>Field of Study</label>
                        <input type="text" name="field_of_study" value="{{ old('field_of_study') }}" class="form-input" placeholder="e.g. Computer Science">
                    </div>
                    <div>
                        <label>Percentage / CGPA</label>
                        <input type="text" name="percentage_cgpa" value="{{ old('percentage_cgpa') }}" class="form-input" placeholder="e.g. 8.2 CGPA / 78%">
                    </div>
                </div>
            </div>

            <div class="tn-apply__section">
                <div class="tn-apply__section-head">
                    <span class="tn-apply__num">4</span>
                    <div>
                        <h2>Experience Details <small>(If Any)</small></h2>
                        <p>Freshers can leave experience fields blank.</p>
                    </div>
                </div>
                <div class="tn-apply__fields tn-apply__fields--2">
                    <div>
                        <label>Total Experience</label>
                        <select name="total_experience" class="form-input">
                            <option value="">Select experience</option>
                            @foreach (['Fresher / No experience', '0–1 years', '1–3 years', '3–5 years', '5+ years'] as $exp)
                                <option value="{{ $exp }}" @selected(old('total_experience') === $exp)>{{ $exp }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label>Current / Last Company</label>
                        <input type="text" name="last_company" value="{{ old('last_company') }}" class="form-input" placeholder="Company name">
                    </div>
                    <div>
                        <label>Job Title</label>
                        <input type="text" name="job_title" value="{{ old('job_title') }}" class="form-input" placeholder="Your role">
                    </div>
                    <div class="tn-apply__full">
                        <label>Responsibilities</label>
                        <textarea name="responsibilities" rows="3" class="form-input" placeholder="Key responsibilities and achievements">{{ old('responsibilities') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="tn-apply__section">
                <div class="tn-apply__section-head">
                    <span class="tn-apply__num">5</span>
                    <div>
                        <h2>Attachments</h2>
                        <p>Upload resume (required) and optional cover letter.</p>
                    </div>
                </div>
                <div class="tn-apply__fields tn-apply__fields--2">
                    <label class="tn-apply__upload">
                        <input type="file" name="resume" accept=".pdf,.doc,.docx" required>
                        <strong>Upload Resume <span>*</span></strong>
                        <em>Click to upload or drag and drop. PDF, DOC, DOCX (Max. 5MB)</em>
                        @error('resume')<p class="tn-apply__error">{{ $message }}</p>@enderror
                    </label>
                    <label class="tn-apply__upload">
                        <input type="file" name="cover_letter" accept=".pdf,.doc,.docx">
                        <strong>Cover Letter (Optional)</strong>
                        <em>Click to upload or drag and drop. PDF, DOC, DOCX (Max. 5MB)</em>
                        @error('cover_letter')<p class="tn-apply__error">{{ $message }}</p>@enderror
                    </label>
                </div>
            </div>

            <div class="tn-apply__section">
                <div class="tn-apply__section-head">
                    <span class="tn-apply__num">6</span>
                    <div>
                        <h2>Additional Information</h2>
                        <p>Help us understand your interest.</p>
                    </div>
                </div>
                <div class="tn-apply__fields">
                    <div>
                        <label>Why do you want to join us?</label>
                        <textarea name="why_join" rows="4" class="form-input" placeholder="Write a short note...">{{ old('why_join') }}</textarea>
                    </div>
                    <div>
                        <label>How did you find about this opening?</label>
                        <select name="source" class="form-input">
                            <option value="">Select option</option>
                            @foreach (['Company Website', 'LinkedIn', 'Referral', 'Campus Drive', 'Social Media', 'Other'] as $src)
                                <option value="{{ $src }}" @selected(old('source') === $src)>{{ $src }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <label class="tn-apply__declare">
                <input type="checkbox" name="declared" value="1" @checked(old('declared')) required>
                <span>I hereby declare that all the information provided above is true and correct to the best of my knowledge. <em>*</em></span>
            </label>
            @error('declared')<p class="tn-apply__error">{{ $message }}</p>@enderror

            <div class="tn-apply__actions">
                <button type="reset" class="tn-btn tn-btn-outline">Reset</button>
                <button type="submit" class="tn-btn tn-btn-primary">Submit Application</button>
            </div>
        </form>

        <aside class="tn-apply__aside">
            <div class="tn-apply__why">
                <h3>Why Join Us?</h3>
                <ul>
                    <li>Great Work Culture</li>
                    <li>Learning & Growth Opportunities</li>
                    <li>Competitive Salary</li>
                    <li>Innovative Environment</li>
                    <li>Work-Life Balance</li>
                </ul>
            </div>
            <div class="tn-apply__help tn-card">
                <h3>Need Help?</h3>
                <p>Our HR team is happy to guide you through the application process.</p>
                <a href="mailto:{{ $site['email'] ?? 'support.kkdigitalsolution@gmail.com' }}">{{ $site['email'] ?? 'support.kkdigitalsolution@gmail.com' }}</a>
                @foreach (($site['phones'] ?? ['+91 93709 21363', '+91 89319 35177']) as $phone)
                    <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}">{{ $phone }}</a>
                @endforeach
                <p class="tn-apply__hours">{{ $site['working_hours'] ?? 'Mon–Sat 9:30 AM – 7:00 PM' }}</p>
            </div>
        </aside>
    </div>
</section>
@endsection
