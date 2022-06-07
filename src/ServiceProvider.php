<?php

namespace Profiler;

use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

/**
 * Class ServiceProvider
 *
 * @package Profiler;
 */
class ServiceProvider extends IlluminateServiceProvider
{
    
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(Profiler::class, function ($app) {
            return new Profiler($app);
        });
        if($this->app->make(Profiler::class)->isEnabled()){
            $this->app->make(Profiler::class)->boot();
        }
    }
}
