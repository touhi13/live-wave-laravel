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
        protected string $apiKey,
        protected string $apiSecret,
        protected string $baseUrl,
        protected int $timeout = 30,
    ) {
        $this->http = Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->withHeaders([
                'X-API-Key' => $this->apiKey,
                'X-API-Secret' => $this->apiSecret,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]);
    }

    /**
     * Broadcast an event to a public channel
     */
    public function broadcast(string $channel, string $event, array $data = []): bool
    {
        return $this->broadcastEvent($channel, $event, $data, 'public');
    }

    /**
     * Broadcast an event to a private channel
     */
    public function broadcastToPrivate(string $channel, string $event, array $data = []): bool
    {
        return $this->broadcastEvent("private-{$channel}", $event, $data, 'private');
    }

    /**
     * Broadcast an event to a presence channel
     */
    public function broadcastToPresence(string $channel, string $event, array $data = []): bool
    {
        return $this->broadcastEvent("presence-{$channel}", $event, $data, 'presence');
    }

    /**
     * Broadcast an event
     */
    protected function broadcastEvent(string $channel, string $event, array $data, string $type): bool
    {
        $response = $this->http->post('/api/broadcast', [
            'channel' => $channel,
            'event' => $event,
            'data' => $data,
            'type' => $type,
        ]);

        return $response->successful();
    }

    /**
     * Broadcast to multiple channels
     */
    public function broadcastToMany(array $channels, string $event, array $data = []): bool
    {
        $response = $this->http->post('/api/broadcast/batch', [
            'channels' => $channels,
            'event' => $event,
            'data' => $data,
        ]);

        return $response->successful();
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
     * Generate webhook signature
     */
    public function generateSignature(string $payload): string
    {
        return hash_hmac('sha256', $payload, $this->apiSecret);
    }

    /**
     * Verify webhook signature
     */
    public function verifySignature(string $payload, string $signature): bool
    {
        $expected = $this->generateSignature($payload);
        return hash_equals($expected, $signature);
    }
}
