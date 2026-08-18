<?php

namespace App\Providers;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);

        $shareSite = function () {
            $site = [
                'name' => 'K&K Digital Solution',
                'tagline' => 'Innovate · Build · Grow',
                'email' => 'support.kkdigitalsolution@gmail.com',
                'phones' => ['+91 93709 21363', '+91 89319 35177'],
                'address' => 'K & K Hub, Jalgaon Jamod, Maharashtra, 443402',
                'address_2' => 'Mandhana, Kanpur Nagar 209217',
                'working_hours' => 'Mon–Sat 9:30 AM – 7:00 PM',
                'socials' => [
                    'instagram' => 'https://www.instagram.com/kkdigitalsolution.official/',
                ],
                'map_embed_url' => 'https://maps.google.com/maps?q=K+%26+K+Hub,+Jalgaon+Jamod,+Maharashtra,+443402&hl=en&z=15&output=embed',
                'directions_url' => 'https://www.google.com/maps/dir/?api=1&destination=K+%26+K+Hub,+Jalgaon+Jamod,+Maharashtra,+443402',
            ];

            try {
                if (Schema::hasTable('site_settings')) {
                    foreach (array_keys($site) as $key) {
                        $value = SiteSetting::get($key);
                        if ($value !== null) {
                            $site[$key] = $value;
                        }
                    }
                    if (is_string($site['email'] ?? null)) {
                        $site['email'] = rtrim(trim($site['email'], " \t\n\r\0\x0B\"'"), '.');
                    }
                }
            } catch (\Throwable) {
                // DB may not be ready during early boot / artisan
            }

            // Keep only active contact numbers + latest addresses
            $site['phones'] = ['+91 93709 21363', '+91 89319 35177'];
            $site['address'] = 'K & K Hub, Jalgaon Jamod, Maharashtra, 443402';
            $site['address_2'] = 'Mandhana, Kanpur Nagar 209217';
            $site['socials'] = [
                'instagram' => 'https://www.instagram.com/kkdigitalsolution.official/',
            ];
            $site['map_embed_url'] = 'https://maps.google.com/maps?q=K+%26+K+Hub,+Jalgaon+Jamod,+Maharashtra,+443402&hl=en&z=15&output=embed';
            $site['directions_url'] = 'https://www.google.com/maps/dir/?api=1&destination=K+%26+K+Hub,+Jalgaon+Jamod,+Maharashtra,+443402';

            return $site;
        };

        View::composer('*', function ($view) use ($shareSite) {
            if (! isset($view->getData()['site'])) {
                $view->with('site', $shareSite());
            }
        });
    }
}
