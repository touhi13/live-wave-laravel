<?php

namespace LiveWave\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'livewave:install
                            {--app-id= : The LiveWave App ID}
                            {--app-key= : The LiveWave App Key}
                            {--app-secret= : The LiveWave App Secret}
                            {--host=127.0.0.1 : The LiveWave server host}
                            {--port=8080 : The LiveWave server port}';

    /**
     * The console command description.
     */
    protected $description = 'Install and configure LiveWave for your Laravel application';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('');
        $this->info('  ╔═══════════════════════════════════════════════╗');
        $this->info('  ║            LiveWave Installation              ║');
        $this->info('  ╚═══════════════════════════════════════════════╝');
        $this->info('');

        // Get credentials
        $appId = $this->option('app-id') ?: $this->ask('Enter your LiveWave App ID');
        $appKey = $this->option('app-key') ?: $this->ask('Enter your LiveWave App Key');
        $appSecret = $this->option('app-secret') ?: $this->secret('Enter your LiveWave App Secret');
        $host = $this->option('host');
        $port = $this->option('port');

        // Publish config
        $this->info('Publishing configuration...');
        $this->call('vendor:publish', [
            '--tag' => 'livewave-config',
            '--force' => true,
        ]);

        // Update .env file
        $this->info('Updating environment file...');
        $this->updateEnvFile([
            'BROADCAST_CONNECTION' => 'livewave',
            'LIVEWAVE_APP_ID' => $appId,
            'LIVEWAVE_APP_KEY' => $appKey,
            'LIVEWAVE_APP_SECRET' => $appSecret,
            'LIVEWAVE_HOST' => $host,
            'LIVEWAVE_PORT' => $port,
        ]);

        // Update broadcasting.php
        $this->info('Configuring broadcasting...');
        $this->updateBroadcastingConfig();

        $this->info('');
        $this->info('  ✓ LiveWave has been installed successfully!');
        $this->info('');
        $this->line('  <fg=gray>Next steps:</>');
        $this->line('  <fg=gray>1. Add the following to your broadcasting.php connections:</>');
        $this->info("     'livewave' => ['driver' => 'livewave']");
        $this->line('');
        $this->line('  <fg=gray>2. Configure Laravel Echo in your frontend:</>');
        $this->info('');
        $this->info('     import Echo from "laravel-echo";');
        $this->info('     import Pusher from "pusher-js";');
        $this->info('');
        $this->info('     window.Echo = new Echo({');
        $this->info('         broadcaster: "pusher",');
        $this->info("         key: \"{$appKey}\",");
        $this->info("         wsHost: \"{$host}\",");
        $this->info("         wsPort: {$port},");
        $this->info('         forceTLS: false,');
        $this->info('         disableStats: true,');
        $this->info('         enabledTransports: ["ws", "wss"],');
        $this->info('     });');
        $this->info('');

        return self::SUCCESS;
    }

    /**
     * Update the .env file with the given key-value pairs.
     */
    protected function updateEnvFile(array $values): void
    {
        $envPath = base_path('.env');

        if (!File::exists($envPath)) {
            return;
        }

        $envContent = File::get($envPath);

        foreach ($values as $key => $value) {
            // Check if key exists
            if (preg_match("/^{$key}=.*/m", $envContent)) {
                // Update existing key
                $envContent = preg_replace(
                    "/^{$key}=.*/m",
                    "{$key}={$value}",
                    $envContent
                );
            } else {
                // Add new key
                $envContent .= "\n{$key}={$value}";
            }
        }

        File::put($envPath, $envContent);
    }

    /**
     * Update the broadcasting.php config file.
     */
    protected function updateBroadcastingConfig(): void
    {
        $configPath = config_path('broadcasting.php');

        if (!File::exists($configPath)) {
            return;
        }

        $content = File::get($configPath);

        // Check if livewave connection already exists
        if (str_contains($content, "'livewave'")) {
            return;
        }

        // Find the connections array and add livewave
        $replacement = "'connections' => [

        'livewave' => [
            'driver' => 'livewave',
        ],";

        $content = str_replace("'connections' => [", $replacement, $content);

        File::put($configPath, $content);
    }
}
