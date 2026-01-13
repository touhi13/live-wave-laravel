<?php

namespace LiveWave\Api;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;

class Webhooks
{
    public function __construct(
        protected PendingRequest $http
    ) {}

    /**
     * Get all webhooks
     */
    public function all(array $params = []): Collection
    {
        $response = $this->http->get('/api/webhooks', $params);

        return collect($response->json('data', []));
    }

    /**
     * Get a specific webhook
     */
    public function find(string $id): ?array
    {
        $response = $this->http->get("/api/webhooks/{$id}");

        if ($response->failed()) {
            return null;
        }

        return $response->json('data');
    }

    /**
     * Create a new webhook
     */
    public function create(array $data): array
    {
        $response = $this->http->post('/api/webhooks', $data);

        return $response->json('data');
    }

    /**
     * Update a webhook
     */
    public function update(string $id, array $data): array
    {
        $response = $this->http->put("/api/webhooks/{$id}", $data);

        return $response->json('data');
    }

    /**
     * Delete a webhook
     */
    public function delete(string $id): bool
    {
        $response = $this->http->delete("/api/webhooks/{$id}");

        return $response->successful();
    }

    /**
     * Get webhook delivery history
     */
    public function deliveries(string $id, array $params = []): Collection
    {
        $response = $this->http->get("/api/webhooks/{$id}/deliveries", $params);

        return collect($response->json('data', []));
    }

    /**
     * Retry a failed delivery
     */
    public function retry(string $webhookId, string $deliveryId): bool
    {
        $response = $this->http->post("/api/webhooks/{$webhookId}/deliveries/{$deliveryId}/retry");

        return $response->successful();
    }

    /**
     * Test a webhook
     */
    public function test(string $id): array
    {
        $response = $this->http->post("/api/webhooks/{$id}/test");

        return $response->json('data', []);
    }
}
