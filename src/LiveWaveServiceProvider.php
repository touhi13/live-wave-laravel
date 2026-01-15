<?php

namespace LiveWave;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use LiveWave\Broadcasting\LiveWaveBroadcaster;
use LiveWave\Console\InstallCommand;

class LiveWaveServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/livewave.php', 'livewave');

        $this->app->singleton(LiveWaveClient::class, function ($app) {
            $config = config('livewave');

            return new LiveWaveClient(
                appId: $config['app_id'],
                appKey: $config['app_key'],
                appSecret: $config['app_secret'],
                host: $config['server']['host'] ?? '127.0.0.1',
                port: $config['server']['port'] ?? 8080,
                scheme: $config['server']['scheme'] ?? 'http',
                path: $config['server']['path'] ?? '',
                timeout: $config['api']['timeout'] ?? 30,
                useTls: $config['options']['use_tls'] ?? false,
                verifySsl: $config['options']['verify_ssl'] ?? true,
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

        // Register Blade directive for Echo config
        Blade::directive('livewaveScripts', function () {
            return "<?php echo app('livewave')->getEchoScripts(); ?>";
        });

        // Register artisan commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
            ]);
        }
    }

    /**
     * Get the Echo scripts HTML
     */
    public function getEchoScripts(): string
    {
        $client = app(LiveWaveClient::class);
        $config = $client->getEchoConfig();

        return '<script>
            window.LiveWaveConfig = ' . json_encode($config) . ';
        </script>';
    }
}
