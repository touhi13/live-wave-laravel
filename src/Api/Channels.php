<?php

namespace LiveWave\Api;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;

class Channels
{
    public function __construct(
        protected PendingRequest $http
    ) {}

    /**
     * Get all channels
     */
    public function all(array $params = []): Collection
    {
        $response = $this->http->get('/api/channels', $params);

        return collect($response->json('data', []));
    }

    /**
     * Get a specific channel
     */
    public function find(string $id): ?array
    {
        $response = $this->http->get("/api/channels/{$id}");

        if ($response->failed()) {
            return null;
        }

        return $response->json('data');
    }

    /**
     * Find a channel by slug
     */
    public function findBySlug(string $slug): ?array
    {
        $response = $this->http->get('/api/channels', ['slug' => $slug]);

        $channels = $response->json('data', []);

        return $channels[0] ?? null;
    }

    /**
     * Create a new channel
     */
    public function create(array $data): array
    {
        $response = $this->http->post('/api/channels', $data);

        return $response->json('data');
    }

    /**
     * Update a channel
     */
    public function update(string $id, array $data): array
    {
        $response = $this->http->put("/api/channels/{$id}", $data);

        return $response->json('data');
    }

    /**
     * Delete a channel
     */
    public function delete(string $id): bool
    {
        $response = $this->http->delete("/api/channels/{$id}");

        return $response->successful();
    }

    /**
     * Get channel statistics
     */
    public function stats(string $id): array
    {
        $response = $this->http->get("/api/channels/{$id}/stats");

        return $response->json('data', []);
    }

    /**
     * Get active connections for a channel
     */
    public function connections(string $id): Collection
    {
        $response = $this->http->get("/api/channels/{$id}/connections");

        return collect($response->json('data', []));
    }

    /**
     * Get channel events
     */
    public function events(string $id, array $params = []): Collection
    {
        $response = $this->http->get("/api/channels/{$id}/events", $params);

        return collect($response->json('data', []));
    }
}
