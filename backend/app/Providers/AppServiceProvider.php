<?php

namespace App\Providers;

use App\Models\DeliveryRequest;
use App\Models\Payment;
use App\Models\SavedAddress;
use App\Policies\DeliveryRequestPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\SavedAddressPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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
        Model::preventLazyLoading(! app()->isProduction());
        JsonResource::withoutWrapping();
        Gate::policy(DeliveryRequest::class, DeliveryRequestPolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(SavedAddress::class, SavedAddressPolicy::class);

        RateLimiter::for('auth', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));
        RateLimiter::for('otp', fn (Request $request) => Limit::perHour(5)->by($request->input('email', $request->ip())));
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)->by($request->user()?->id ?: $request->ip()));
    }
}
