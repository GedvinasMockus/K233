<?php

namespace App\Providers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

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
        Validator::extend('alpha_numbers_spaces_minus', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/^[\pL\pN\s\-\.]+$/u', $value);
        }, 'The :attribute may only contain letters, numbers, spaces, minuses, and dots.');
        Validator::extend('alpha_num_dash', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/^[a-zA-Z0-9\-]+$/', $value);
        }, 'The :attribute may only contain letters, numbers, and dashes.');
    }
}
