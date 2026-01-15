<?php

namespace LiveWave;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use LiveWave\Api\Channels;
use LiveWave\Api\Webhooks;
use LiveWave\Notifications\NotificationBuilder;

class LiveWaveClient
{
    protected PendingRequest $http;
    protected ?Channels $channels = null;
    protected ?Webhooks $webhooks = null;

    public function __construct(
        protected string $appId,
        protected string $appKey,
        protected string $appSecret,
        protected string $host = '127.0.0.1',
        protected int $port = 8080,
        protected string $scheme = 'http',
        protected string $path = '',
        protected int $timeout = 30,
        protected bool $useTls = false,
        protected bool $verifySsl = true,
    ) {
        $baseUrl = $this->buildBaseUrl();

        $this->http = Http::baseUrl($baseUrl)
            ->timeout($this->timeout)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]);

        if (!$this->verifySsl) {
            $this->http->withoutVerifying();
        }
    }

    /**
     * Build the base URL for API requests
     */
    protected function buildBaseUrl(): string
    {
        $scheme = $this->useTls ? 'https' : $this->scheme;
        $path = $this->path ? '/' . ltrim($this->path, '/') : '';

        return "{$scheme}://{$this->host}:{$this->port}{$path}";
    }

    /**
     * Broadcast an event to a single channel (Pusher-compatible)
     */
    public function trigger(string|array $channels, string $event, array $data = [], ?string $socketId = null): bool
    {
        $channels = is_array($channels) ? $channels : [$channels];

        $body = [
            'name' => $event,
            'channels' => $channels,
            'data' => json_encode($data),
        ];

        if ($socketId) {
            $body['socket_id'] = $socketId;
        }

        $path = "/apps/{$this->appId}/events";
        $queryString = $this->buildQueryString('POST', $path, $body);

        $response = $this->http->post($path . '?' . $queryString, $body);

        return $response->successful();
    }

    /**
     * Broadcast an event to a public channel
     */
    public function broadcast(string $channel, string $event, array $data = []): bool
    {
        return $this->trigger($channel, $event, $data);
    }

    /**
     * Broadcast an event to a private channel
     */
    public function broadcastToPrivate(string $channel, string $event, array $data = []): bool
    {
        $channelName = str_starts_with($channel, 'private-') ? $channel : "private-{$channel}";
        return $this->trigger($channelName, $event, $data);
    }

    /**
     * Broadcast an event to a presence channel
     */
    public function broadcastToPresence(string $channel, string $event, array $data = []): bool
    {
        $channelName = str_starts_with($channel, 'presence-') ? $channel : "presence-{$channel}";
        return $this->trigger($channelName, $event, $data);
    }

    /**
     * Broadcast to multiple channels
     */
    public function broadcastToMany(array $channels, string $event, array $data = []): bool
    {
        return $this->trigger($channels, $event, $data);
    }

    /**
     * Build the query string with auth signature (Pusher-compatible)
     */
    protected function buildQueryString(string $method, string $path, array $body = []): string
    {
        $params = [
            'auth_key' => $this->appKey,
            'auth_timestamp' => time(),
            'auth_version' => '1.0',
        ];

        if (!empty($body)) {
            $params['body_md5'] = md5(json_encode($body));
        }

        ksort($params);

        $stringToSign = implode("\n", [
            $method,
            $path,
            http_build_query($params),
        ]);

        $params['auth_signature'] = hash_hmac('sha256', $stringToSign, $this->appSecret);

        return http_build_query($params);
    }

    /**
     * Generate auth signature for channel authentication
     */
    public function generateSignature(string $stringToSign): string
    {
        $signature = hash_hmac('sha256', $stringToSign, $this->appSecret);
        return "{$this->appKey}:{$signature}";
    }

    /**
     * Authenticate a private channel subscription
     */
    public function authorizeChannel(string $socketId, string $channelName): array
    {
        $stringToSign = "{$socketId}:{$channelName}";

        return [
            'auth' => $this->generateSignature($stringToSign),
        ];
    }

    /**
     * Authenticate a presence channel subscription
     */
    public function authorizePresenceChannel(string $socketId, string $channelName, string $userId, array $userInfo = []): array
    {
        $channelData = json_encode([
            'user_id' => $userId,
            'user_info' => $userInfo,
        ]);

        $stringToSign = "{$socketId}:{$channelName}:{$channelData}";

        return [
            'auth' => $this->generateSignature($stringToSign),
            'channel_data' => $channelData,
        ];
    }

    /**
     * Get channel info
     */
    public function getChannelInfo(string $channelName, array $info = []): ?array
    {
        $path = "/apps/{$this->appId}/channels/{$channelName}";
        $queryString = $this->buildQueryString('GET', $path);

        if (!empty($info)) {
            $queryString .= '&info=' . implode(',', $info);
        }

        $response = $this->http->get($path . '?' . $queryString);

        return $response->successful() ? $response->json() : null;
    }

    /**
     * Get all channels
     */
    public function getChannels(string $prefix = '', array $info = []): ?array
    {
        $path = "/apps/{$this->appId}/channels";
        $queryString = $this->buildQueryString('GET', $path);

        if ($prefix) {
            $queryString .= '&filter_by_prefix=' . $prefix;
        }

        if (!empty($info)) {
            $queryString .= '&info=' . implode(',', $info);
        }

        $response = $this->http->get($path . '?' . $queryString);

        return $response->successful() ? $response->json() : null;
    }

    /**
     * Get presence channel users
     */
    public function getPresenceUsers(string $channelName): ?array
    {
        $path = "/apps/{$this->appId}/channels/{$channelName}/users";
        $queryString = $this->buildQueryString('GET', $path);

        $response = $this->http->get($path . '?' . $queryString);

        return $response->successful() ? $response->json() : null;
    }

    /**
     * Access the Channels API
     */
    public function channels(): Channels
    {
        if (!$this->channels) {
            $this->channels = new Channels($this->http);
        }

        return $this->channels;
    }

    /**
     * Access the Webhooks API
     */
    public function webhooks(): Webhooks
    {
        if (!$this->webhooks) {
            $this->webhooks = new Webhooks($this->http);
        }

        return $this->webhooks;
    }

    /**
     * Create a notification builder
     */
    public function notify(): NotificationBuilder
    {
        return new NotificationBuilder($this->http);
    }

    /**
     * Get the HTTP client
     */
    public function getHttpClient(): PendingRequest
    {
        return $this->http;
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $expected = hash_hmac('sha256', $payload, $this->appSecret);
        return hash_equals($expected, $signature);
    }

    /**
     * Get app credentials for Echo configuration
     */
    public function getEchoConfig(): array
    {
        return [
            'broadcaster' => 'pusher',
            'key' => $this->appKey,
            'cluster' => 'mt1',
            'wsHost' => $this->host,
            'wsPort' => $this->port,
            'wssPort' => $this->port,
            'forceTLS' => $this->useTls,
            'enabledTransports' => ['ws', 'wss'],
            'disableStats' => true,
        ];
    }

    /**
     * Get the app ID
     */
    public function getAppId(): string
    {
        return $this->appId;
    }

    /**
     * Get the app key
     */
    public function getAppKey(): string
    {
        return $this->appKey;
    }
}
