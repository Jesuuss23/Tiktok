<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
if (config('app.env') !== 'local') {
        \Illuminate\Support\Facades\URL::forceRootUrl('https://auto.donguando.com/tiktok-api');
        \Illuminate\Support\Facades\URL::forceScheme('https');
    }

    // Tu configuración de Socialite que ya tienes...
    \Illuminate\Support\Facades\Event::listen(
        \SocialiteProviders\Manager\SocialiteWasCalled::class,
        [\SocialiteProviders\TikTok\TikTokExtendSocialite::class, 'handle']
    );
    }
}
