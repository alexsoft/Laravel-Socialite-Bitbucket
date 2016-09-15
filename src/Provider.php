<?php

namespace Alexsoft\LaravelSocialiteBitbucket;

use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Contracts\Factory;

class Provider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $socialite = $this->app->make(Factory::class);

        $config = config()->get('services.bitbucket');

        $provider = $socialite->buildProvider(BitbucketSocialiteProvider::class, $config);

        $socialite->extend('bitbucket2', function() use ($provider) {
            return $provider;
        });
    }
}