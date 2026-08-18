<footer class="tn-footer">
    <div class="container-tn grid gap-10 md:grid-cols-4">
        <div class="md:col-span-1">
            <a href="{{ route('home') }}" class="tn-logo tn-logo--footer" aria-label="KK Digital Solution">
                <img src="{{ asset('images/kk-logo.png') }}" alt="KK Digital Solution" width="72" height="72">
            </a>
            <p class="tn-footer__text">We deliver innovative, scalable and secure IT solutions that help businesses transform, grow and lead in the digital era.</p>
        </div>
        <div>
            <h3 class="tn-footer__title">Company</h3>
            <div class="flex flex-col gap-2.5 text-sm">
                <a href="{{ route('about') }}">About Us</a>
                <a href="{{ route('careers') }}">Careers</a>
                <a href="{{ route('case-studies') }}">Portfolio</a>
            </div>
        </div>
        <div>
            <h3 class="tn-footer__title">Explore</h3>
            <div class="flex flex-col gap-2.5 text-sm">
                <a href="{{ route('start-project') }}">Get a Quote</a>
                <a href="{{ route('contact') }}">Contact</a>
            </div>
        </div>
        <div>
            <h3 class="tn-footer__title">Contact</h3>
            <div class="flex flex-col gap-2.5 text-sm">
                <a href="mailto:{{ $site['email'] ?? 'support.kkdigitalsolution@gmail.com' }}">{{ $site['email'] ?? 'support.kkdigitalsolution@gmail.com' }}</a>
                @foreach (($site['phones'] ?? ['+91 93709 21363', '+91 89319 35177']) as $phone)
                    <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}">{{ $phone }}</a>
                @endforeach
                <p class="m-0 text-[#94a3b8]">{{ $site['address'] ?? '' }}</p>
                @if (! empty($site['address_2']))
                    <p class="m-0 text-[#94a3b8]">{{ $site['address_2'] }}</p>
                @endif
                @php $instagram = $site['socials']['instagram'] ?? 'https://www.instagram.com/kkdigitalsolution.official/'; @endphp
                <a href="{{ $instagram }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 mt-1 text-[#94a3b8] hover:text-white" aria-label="Instagram">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/>
                    </svg>
                    <span>Instagram</span>
                </a>
            </div>
        </div>
    </div>
    <div class="tn-footer__bottom">
        <div class="container-tn flex flex-col gap-2 sm:flex-row sm:justify-between">
            <p class="m-0">&copy; {{ date('Y') }} K&K. All rights reserved.</p>
            <div class="flex gap-4">
                <a href="{{ route('privacy') }}">Privacy</a>
                <a href="{{ route('terms') }}">Terms</a>
            </div>
        </div>
    </div>
</footer>
