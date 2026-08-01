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
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use ImageKit\ImageKit;
use Inertia\ExceptionResponse;
use Inertia\Inertia;
use League\Flysystem\Filesystem;
use TaffoVelikoff\ImageKitAdapter\ImagekitAdapter;

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
        $this->configureStorage();
        $this->configureRateLimiting();
        $this->configureErrorHandling();
    }

    /**
     * Register the ImageKit Flysystem driver.
     */
    protected function configureStorage(): void
    {
        Storage::extend('imagekit', function ($app, $config) {
            $adapter = new ImagekitAdapter(
                new ImageKit(
                    $config['public_key'],
                    $config['private_key'],
                    $config['endpoint_url'],
                ),
            );

            return new FilesystemAdapter(
                new Filesystem($adapter, $config),
                $adapter,
                $config,
            );
        });
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('whatsapp-webhooks', fn (Request $request) => Limit::perMinute(60)
            ->by('whatsapp-webhook:'.$request->ip()));
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

        Password::defaults(
            fn (): ?Password => app()->isProduction()
                ? Password::min(12)
                    ->mixedCase()
                    ->letters()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
                : null,
        );
    }

    /**
     * Render a branded Inertia error page for HTTP errors in production.
     * Left alone in local/testing so Laravel's debug error screens still show.
     */
    protected function configureErrorHandling(): void
    {
        if (! app()->isProduction()) {
            return;
        }

        Inertia::handleExceptionsUsing(function (ExceptionResponse $response) {
            if (in_array($response->statusCode(), [403, 404, 500, 503], true)) {
                return $response->render('error-page', [
                    'status' => $response->statusCode(),
                ])->withSharedData();
            }
        });
    }
}
