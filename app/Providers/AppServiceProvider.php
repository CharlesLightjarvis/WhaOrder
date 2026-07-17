<?php

namespace App\Providers;

use App\Repositories\Addresses\AddressRepository;
use App\Repositories\Addresses\EloquentAddressRepository;
use App\Repositories\Categories\CategoryRepository;
use App\Repositories\Categories\EloquentCategoryRepository;
use App\Repositories\Conversations\ConversationRepository;
use App\Repositories\Conversations\EloquentConversationRepository;
use App\Repositories\Customers\CustomerRepository;
use App\Repositories\Customers\EloquentCustomerRepository;
use App\Repositories\Orders\EloquentOrderRepository;
use App\Repositories\Orders\OrderRepository;
use App\Repositories\Products\EloquentProductRepository;
use App\Repositories\Products\ProductRepository;
use App\Repositories\WhatsAppSessions\EloquentWhatsAppSessionRepository;
use App\Repositories\WhatsAppSessions\WhatsAppSessionRepository;
use App\Services\Waha\WahaClient;
use Carbon\CarbonImmutable;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ProductRepository::class, EloquentProductRepository::class);
        $this->app->bind(CategoryRepository::class, EloquentCategoryRepository::class);
        $this->app->bind(CustomerRepository::class, EloquentCustomerRepository::class);
        $this->app->bind(AddressRepository::class, EloquentAddressRepository::class);
        $this->app->bind(OrderRepository::class, EloquentOrderRepository::class);
        $this->app->bind(ConversationRepository::class, EloquentConversationRepository::class);
        $this->app->bind(WhatsAppSessionRepository::class, EloquentWhatsAppSessionRepository::class);

        $this->app->singleton(WahaClient::class, fn () => new WahaClient(
            baseUrl: config('services.waha.base_url'),
            apiKey: config('services.waha.api_key'),
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        JsonResource::withoutWrapping();

        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
