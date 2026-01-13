<?php

namespace LiveWave;

use Illuminate\Support\ServiceProvider;
use LiveWave\Broadcasting\LiveWaveBroadcaster;

class LiveWaveServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/livewave.php', 'livewave');

        $this->app->singleton(LiveWaveClient::class, function ($app) {
            return new LiveWaveClient(
                apiKey: config('livewave.api_key'),
                apiSecret: config('livewave.api_secret'),
                baseUrl: config('livewave.base_url'),
                timeout: config('livewave.timeout', 30),
            );
        });

        $this->app->alias(LiveWaveClient::class, 'livewave');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish config
        $this->publishes([
            __DIR__ . '/../config/livewave.php' => config_path('livewave.php'),
        ], 'livewave-config');

        // Register broadcast driver
        $this->app->make('Illuminate\Broadcasting\BroadcastManager')
            ->extend('livewave', function ($app, $config) {
                return new LiveWaveBroadcaster(
                    $app->make(LiveWaveClient::class)
                );
            });

        // Register middleware
        $router = $this->app->make('router');
        $router->aliasMiddleware('livewave.webhook', \LiveWave\Http\Middleware\VerifyWebhookSignature::class);
    }
}
