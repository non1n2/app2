<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::composer('layouts.navbar', function ($view) {
            $user = null;
            if(Auth::check()){
                $user = Auth::user();
            }
            $view->with('user_data', $user);
            $available_locales = config('app.available_locales');
            $current_locale = app()->getLocale();
            $view->with('current_locale', $current_locale);
            $view->with('available_locales', $available_locales);
        });
    }
}
