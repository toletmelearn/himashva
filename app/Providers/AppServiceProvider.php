<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\ChatbotResponse;
use App\Models\Product;
use App\Observers\CategoryObserver;
use App\Observers\ChatbotResponseObserver;
use App\Observers\ProductObserver;
use App\Services\Payment\PaymentGatewayManager;
use App\Services\Shipping\ShippingManager;
use App\Services\ShippingServiceInterface;
use App\Services\ShiprocketService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ShippingServiceInterface::class, ShiprocketService::class);
        $this->app->singleton(PaymentGatewayManager::class);
        $this->app->singleton(ShippingManager::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ChatbotResponse::observe(ChatbotResponseObserver::class);
        Category::observe(CategoryObserver::class);
        Product::observe(ProductObserver::class);
    }
}
