<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\CaseStudy;
use App\Models\Faq;
use App\Models\Industry;
use App\Models\JobOpening;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@kkdigital.com'],
            [
                'name' => 'KK Digital Solution Admin',
                'password' => Hash::make('Admin@123'),
                'role' => 'admin',
                'employee_id' => null,
            ]
        );

        $settings = [
            'name' => 'K&K Digital Solution',
            'short_name' => 'KK DIGITAL SOLUTION',
            'tagline' => 'Build. Scale. Grow.',
            'description' => 'End-to-end digital solutions — websites, mobile apps, AI products, and growth programs for startups and enterprises.',
            'email' => 'support.kkdigitalsolution@gmail.com',
            'phones' => ['+91 93709 21363', '+91 89319 35177'],
            'address' => 'K & K Hub, Jalgaon Jamod, Maharashtra, 443402',
            'location_line' => 'K & K Hub, Jalgaon Jamod, Maharashtra',
            'map_embed_url' => 'https://maps.google.com/maps?q=K+%26+K+Hub,+Jalgaon+Jamod,+Maharashtra,+443402&hl=en&z=15&output=embed',
            'directions_url' => 'https://www.google.com/maps/dir/?api=1&destination=K+%26+K+Hub,+Jalgaon+Jamod,+Maharashtra,+443402',
            'working_hours' => 'Mon–Sat 9:30 AM – 7:00 PM',
            'socials' => [
                'linkedin' => 'https://linkedin.com',
                'twitter' => 'https://twitter.com',
                'github' => 'https://github.com',
                'instagram' => 'https://instagram.com',
                'facebook' => 'https://facebook.com',
            ],
            'home_stats' => [
                ['value' => '5+', 'label' => 'Years in Delivery'],
                ['value' => '50+', 'label' => 'Projects Delivered'],
                ['value' => '98%', 'label' => 'Client Satisfaction'],
                ['value' => '24/7', 'label' => 'Support Available'],
            ],
            'trusted_logos' => [
                'Startup India', 'CoinDCX', 'Practo', 'Razorpay',
                "BYJU'S", 'Livspace', 'bitsCrunch', 'Unacademy',
            ],
        ];

        foreach ($settings as $key => $value) {
            SiteSetting::set($key, $value);
        }

        $services = [
            [
                'slug' => 'web',
                'number' => '01',
                'title' => 'Web Development',
                'description' => 'Fast, scalable websites and dashboards using Next.js, React, CMS & APIs.',
                'long_description' => 'We craft high-performance websites, admin panels, and SaaS dashboards that load fast, rank well, and scale with your business.',
                'features' => ['Next.js / React Applications', 'E-commerce & CMS Platforms', 'API Integration & Backend', 'SEO & Performance Optimization'],
                'color' => 'cyan',
            ],
            [
                'slug' => 'app',
                'number' => '02',
                'title' => 'App Development',
                'description' => 'Native-feel mobile apps for iOS & Android using React Native, Flutter & more.',
                'long_description' => 'From MVP to App Store launch — we build smooth, native-feel mobile experiences that users love to open every day.',
                'features' => ['React Native & Flutter', 'iOS & Android Native Feel', 'Push Notifications & Payments', 'App Store / Play Store Launch'],
                'color' => 'teal',
            ],
            [
                'slug' => 'marketing',
                'number' => '03',
                'title' => 'Digital Marketing',
                'description' => 'SEO, paid ads, content & analytics to grow your brand with clear ROI.',
                'long_description' => 'Data-backed growth programs that bring qualified traffic, conversions, and measurable ROI — not vanity metrics.',
                'features' => ['SEO & Content Strategy', 'Google & Meta Ads', 'Analytics & Conversion Tracking', 'Brand & Funnel Optimization'],
                'color' => 'green',
            ],
            [
                'slug' => 'ai',
                'number' => '04',
                'title' => 'AI & Intelligent Products',
                'description' => 'LLM integrations, automation & AI copilots that deliver real business impact.',
                'long_description' => 'We embed AI where it creates leverage — copilots, chatbots, automation workflows, and intelligent product features.',
                'features' => ['LLM Integration & RAG', 'AI Chatbots & Copilots', 'Workflow Automation', 'Custom AI Product Features'],
                'color' => 'purple',
            ],
        ];

        foreach ($services as $i => $service) {
            Service::query()->create([...$service, 'sort_order' => $i + 1]);
        }

        $industries = [
            ['crypto', 'Crypto & Web3', 'Exchanges, wallets, NFT platforms, and blockchain products with secure, scalable architecture.', 'gold'],
            ['health', 'HealthTech', 'Telemedicine, patient apps, and healthcare platforms built for trust, privacy, and compliance.', 'blue'],
            ['edtech', 'EdTech', 'Learning platforms, LMS, and student apps that make education engaging and measurable.', 'purple'],
            ['realestate', 'Real Estate', 'Listings, CRM, virtual tours, and property tech that convert browsers into buyers.', 'sky'],
            ['urban', 'Urban & Home Services', 'On-demand service apps for home, city, and lifestyle needs — booking to fulfillment.', 'orange'],
            ['fintech', 'FinTech', 'Payments, lending, dashboards, and financial products designed for speed and security.', 'violet'],
            ['retail', 'Retail & E-commerce', 'Storefronts, marketplaces, and commerce engines that drive sales and loyalty.', 'pink'],
            ['ai-auto', 'AI & Automation', 'Intelligent workflows, copilots, and automation that cut cost and unlock scale.', 'cyan'],
            ['logistics', 'Logistics & Supply Chain', 'Tracking, fleet, warehouse, and logistics platforms for real-time operations.', 'green'],
            ['media', 'Media & Entertainment', 'Streaming, content platforms, and engagement products built for audiences at scale.', 'red'],
            ['travel', 'Travel & Hospitality', 'Booking engines, hotel apps, and travel experiences that delight every guest.', 'teal'],
            ['manufacturing', 'Manufacturing', 'Industrial dashboards, IoT panels, and ops tools that modernize the factory floor.', 'yellow'],
        ];

        foreach ($industries as $i => [$slug, $title, $description, $color]) {
            Industry::query()->create([
                'slug' => $slug,
                'title' => $title,
                'description' => $description,
                'color' => $color,
                'sort_order' => $i + 1,
                'is_featured' => $i < 6,
            ]);
        }

        $cases = [
            [
                'LoveCare Connected',
                'HealthTech',
                'Live website',
                'Care and wellness platform connecting patients and providers with a clear, conversion-focused digital experience.',
                'https://lovecareconnected.com/',
                null,
            ],
            [
                'Anayaraa',
                'E-Commerce',
                'Live marketplace',
                'Full-stack shopping experience for fashion, beauty, home & lifestyle — shop smart, sell easily.',
                'https://anayaraa.com/',
                'videos/portfolio/anayaraa.mp4',
            ],
            [
                'Darbar Fresh',
                'Food & Delivery',
                'Live product demo',
                'Fresh food and delivery experience with a clean, conversion-focused digital storefront.',
                'https://darbarfresh.com/',
                'videos/portfolio/darbarfresh.mp4',
            ],
            [
                'Flitpay',
                'FinTech',
                'Live product',
                'Digital payments experience designed for fast, reliable and secure money movement.',
                'https://flitpay.com/',
                'videos/portfolio/flitpay.mp4',
            ],
            [
                'tradefair',
                'Events & Marketplace',
                'Live platform',
                'Exhibition marketplace connecting organizers, exhibitors, visitors and booth service providers across India.',
                'https://tradefair.com/',
                'videos/portfolio/tradefair.mp4',
            ],
            [
                'Daily Khata',
                'FinTech',
                'Live product',
                'Digital ledger for shops and small businesses — track daily sales, expenses, credit and customer balances in one place.',
                'https://dailykhata.in/',
                'videos/portfolio/dailykhata.mp4',
            ],
            [
                'kinaracafe',
                'Hospitality',
                'Live website',
                'Brand website for Kinara Cafe — warm hospitality presence with a polished digital front.',
                'https://kinaracafe.com/',
                'videos/portfolio/kinaracafe.mp4',
            ],
            [
                'Oshu',
                'Logistics',
                'Live platform',
                'Smart logistics platform for booking vehicles, live tracking and reliable deliveries across India.',
                'https://oshu.in/',
                'videos/portfolio/oshu.mp4',
            ],
            [
                'frontdoorfix',
                'Home Services',
                'Live platform',
                'Trusted home services platform — book verified professionals for cleaning, repairs, salon and more.',
                'https://frontdoorfix.in/',
                'videos/portfolio/frontdoorfix.mp4',
            ],
            [
                'Subhvivahsanskar',
                'Matrimony',
                'Live platform',
                'Verified matrimony platform with trust-first matching, sacred values and proximity-based discovery.',
                'https://subhvivahsanskar.com/',
                'videos/portfolio/subhvivahsanskar.mp4',
            ],
            [
                'Campus Oraa',
                'EdTech',
                'Live platform',
                'Learning platform for skills and school education — courses, enrollment and student success journeys.',
                'https://campusoraa.com/',
                'videos/portfolio/campusoraa.mp4',
            ],
            [
                'Subete',
                'E-Commerce',
                'Live marketplace',
                'Multi-vendor marketplace connecting electronics, fashion, grocery and home essentials — shop smart, sell easily.',
                'https://subete.in/',
                'videos/portfolio/arwater-tank-1.mp4',
            ],
            [
                'Mbio',
                'HealthTech',
                'Live website',
                'Biomedical digital presence for products and services — clean brand experience built for trust and conversions.',
                'https://mbiomedical.in',
                'videos/portfolio/arwater-tank-2.mp4',
            ],
            [
                'Arwater Tank',
                'Home Services',
                'Live website',
                'Professional water tank cleaning service website — bookings, service showcase and conversion-focused digital presence.',
                'https://arwatertankcleaner.in',
                'videos/portfolio/arwater-tank-3.mp4',
            ],
            [
                'buildcourseai',
                'EdTech',
                'Live platform',
                'AI-powered course builder to create, launch and scale learning experiences for students and educators.',
                'https://buildcourseai.com/',
                'videos/portfolio/buildcourseai.mp4',
            ],
            [
                'CRM Pro',
                'Software',
                'Custom software',
                'CRM platform for managing leads, customers, follow-ups and sales pipelines in one place.',
                null,
                null,
            ],
        ];

        foreach ($cases as $i => [$title, $industry, $result, $summary, $url, $video]) {
            $baseSlug = Str::slug($title);
            $slug = $baseSlug;
            if (CaseStudy::query()->where('slug', $slug)->exists()) {
                $slug = $baseSlug.'-'.($i + 1);
            }

            CaseStudy::query()->create([
                'slug' => $slug,
                'title' => $title,
                'industry' => $industry,
                'folder' => match ($title) {
                    'm bio', 'kinara cafe' => 'mobile',
                    'CRM Pro' => 'software',
                    default => 'website',
                },
                'result' => $result,
                'summary' => $summary,
                'project_url' => $url,
                'video' => $video,
                'sort_order' => $i + 1,
            ]);
        }

        $categories = [
            'Web Development', 'Mobile Apps', 'AI & Automation', 'Digital Marketing', 'Productivity',
        ];

        $categoryIds = [];
        foreach ($categories as $name) {
            $cat = BlogCategory::query()->create([
                'name' => $name,
                'slug' => Str::slug($name),
            ]);
            $categoryIds[$name] = $cat->id;
        }

        $posts = [
            ['nextjs-14-whats-new', 'Next.js 14: What\'s New and Why It Matters', 'A practical look at the App Router, Server Actions, and performance wins that change how we ship production apps.', 'Web Development', '2025-05-22', 6, 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=800&q=80'],
            ['ai-copilots-business', 'How AI Copilots Are Transforming Business Workflows', 'From support desks to internal ops — where LLM integrations create real ROI and where they don\'t.', 'AI & Automation', '2025-05-18', 8, 'https://images.unsplash.com/photo-1677442136019-21780ecad995?w=800&q=80'],
            ['react-native-vs-flutter', 'React Native vs Flutter in 2025: Choosing Right', 'A founder-friendly comparison of speed, talent, performance, and long-term maintainability.', 'Mobile Apps', '2025-05-12', 7, 'https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?w=800&q=80'],
            ['seo-for-saas', 'SEO for SaaS: A Playbook That Actually Converts', 'Technical SEO, content clusters, and conversion paths that turn organic traffic into trials.', 'Digital Marketing', '2025-05-05', 9, 'https://images.unsplash.com/photo-1432888498266-38ffec3eaf0a?w=800&q=80'],
            ['shipping-faster', 'Ship Faster Without Breaking Quality', 'Sprint rituals, design systems, and QA habits that keep velocity high and bugs low.', 'Productivity', '2025-04-28', 5, 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=800&q=80'],
            ['building-ai-products', 'Building AI Products Users Actually Trust', 'UX patterns, evaluation loops, and guardrails for shipping intelligent features responsibly.', 'AI & Automation', '2025-04-20', 10, 'https://images.unsplash.com/photo-1620712943543-bcc4688e7485?w=800&q=80'],
        ];

        foreach ($posts as $i => [$slug, $title, $excerpt, $category, $date, $mins, $image]) {
            BlogPost::query()->create([
                'blog_category_id' => $categoryIds[$category],
                'slug' => $slug,
                'title' => $title,
                'excerpt' => $excerpt,
                'body' => "<p>{$excerpt}</p><p>K&K Digital Solution helps teams ship modern products with clarity, speed, and measurable outcomes.</p>",
                'cover_image' => $image,
                'read_time_mins' => $mins,
                'is_popular' => $i < 3,
                'published_at' => $date,
            ]);
        }

        $jobs = [
            ['Full-Stack Engineer (Next.js)', 'Full-time · Remote / Hybrid', 'Kanpur / Remote'],
            ['React Native Developer', 'Full-time · Remote', 'India'],
            ['UI/UX Designer', 'Full-time · Hybrid', 'Kanpur'],
            ['Digital Marketing Specialist', 'Full-time · Remote', 'India'],
            ['Internship for Skill Upgrade', 'Internship · Remote / Hybrid', 'Kanpur / Remote'],
        ];

        foreach ($jobs as $i => [$title, $type, $location]) {
            JobOpening::query()->create([
                'title' => $title,
                'employment_type' => $type,
                'location' => $location,
                'sort_order' => $i + 1,
            ]);
        }

        $faqs = [
            ['How long does a typical project take?', 'MVPs often ship in 4–12 weeks depending on scope. We share a clear timeline after discovery.'],
            ['Do you work with startups and enterprises?', 'Yes — we partner with early-stage founders and growing teams across industries.'],
            ['What technologies do you use?', 'Next.js, React, React Native, Flutter, Node, Laravel, cloud platforms, and modern AI/LLM stacks.'],
            ['How do we get started?', 'Click Start Your Project or Contact Us — we’ll reply within 24 hours.'],
        ];

        foreach ($faqs as $i => [$q, $a]) {
            Faq::query()->create([
                'question' => $q,
                'answer' => $a,
                'sort_order' => $i + 1,
            ]);
        }

        TeamMember::query()->delete();

        $people = [
            [
                'name' => 'Prasenjit Mishra',
                'role' => 'Founder',
                'bio' => 'Strategy, partnerships and delivery excellence across client engagements.',
                'phone' => '9370921363',
                'group' => 'founder',
                'photo' => 'images/team/prasenjit-mishra.png',
                'sort_order' => 1,
            ],
            [
                'name' => 'Divyanshu Mishra',
                'role' => 'Founder',
                'bio' => 'Product engineering and scalable systems with modern technology stacks.',
                'phone' => '8931935177',
                'group' => 'founder',
                'photo' => 'images/team/divyanshu-mishra.png',
                'sort_order' => 2,
            ],
            [
                'name' => 'Yashi Sachan',
                'role' => 'Project Head',
                'bio' => 'Owns project delivery end-to-end — timelines, client communication and quality outcomes across engagements.',
                'phone' => null,
                'group' => 'team',
                'photo' => 'images/team/yashi-sachan.png',
                'sort_order' => 3,
            ],
            [
                'name' => 'Tapaswi Tiwari',
                'role' => 'Digital Marketing Head',
                'bio' => 'Leads brand growth, campaigns and digital presence so K&K reaches the right audiences with the right message.',
                'phone' => null,
                'group' => 'team',
                'photo' => 'images/team/tapaswi-tiwari.png',
                'sort_order' => 4,
            ],
            [
                'name' => 'Pranjal Ithapr',
                'role' => 'Internship',
                'bio' => 'Supporting delivery and learning modern product workflows as part of the K&K internship program.',
                'phone' => null,
                'group' => 'team',
                'photo' => 'images/team/pranjal-ithapr.png',
                'sort_order' => 5,
            ],
        ];

        foreach ($people as $person) {
            TeamMember::query()->create($person);
        }
    }
}
