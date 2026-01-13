<?php

namespace LiveWave\Testing;

use Illuminate\Support\Collection;
use LiveWave\Api\Channels;
use LiveWave\Api\Webhooks;
use LiveWave\Notifications\NotificationBuilder;
use PHPUnit\Framework\Assert as PHPUnit;

class LiveWaveFake
{
    protected array $broadcasts = [];
    protected array $notifications = [];

    /**
     * Fake broadcast
     */
    public function broadcast(string $channel, string $event, array $data = []): bool
    {
        $this->broadcasts[] = [
            'channel' => $channel,
            'event' => $event,
            'data' => $data,
            'type' => 'public',
        ];

        return true;
    }

    /**
     * Fake private broadcast
     */
    public function broadcastToPrivate(string $channel, string $event, array $data = []): bool
    {
        $this->broadcasts[] = [
            'channel' => "private-{$channel}",
            'event' => $event,
            'data' => $data,
            'type' => 'private',
        ];

        return true;
    }

    /**
     * Fake presence broadcast
     */
    public function broadcastToPresence(string $channel, string $event, array $data = []): bool
    {
        $this->broadcasts[] = [
            'channel' => "presence-{$channel}",
            'event' => $event,
            'data' => $data,
            'type' => 'presence',
        ];

        return true;
    }

    /**
     * Fake batch broadcast
     */
    public function broadcastToMany(array $channels, string $event, array $data = []): bool
    {
        foreach ($channels as $channel) {
            $this->broadcasts[] = [
                'channel' => $channel,
                'event' => $event,
                'data' => $data,
                'type' => 'batch',
            ];
        }

        return true;
    }

    /**
     * Get channels API (returns fake)
     */
    public function channels(): ChannelsFake
    {
        return new ChannelsFake();
    }

    /**
     * Get webhooks API (returns fake)
     */
    public function webhooks(): WebhooksFake
    {
        return new WebhooksFake();
    }

    /**
     * Create notification builder (returns fake)
     */
    public function notify(): NotificationBuilderFake
    {
        return new NotificationBuilderFake($this);
    }

    /**
     * Record a notification
     */
    public function recordNotification(array $notification): void
    {
        $this->notifications[] = $notification;
    }

    /**
     * Assert an event was broadcast
     */
    public function assertBroadcast(string $channel, string $event, ?callable $callback = null): void
    {
        $matching = collect($this->broadcasts)->filter(function ($broadcast) use ($channel, $event, $callback) {
            $matches = $broadcast['channel'] === $channel && $broadcast['event'] === $event;

            if ($matches && $callback) {
                return $callback($broadcast['data']);
            }

            return $matches;
        });

        PHPUnit::assertTrue(
            $matching->count() > 0,
            "Failed asserting that event [{$event}] was broadcast to channel [{$channel}]."
        );
    }

    /**
     * Assert an event was broadcast to a private channel
     */
    public function assertBroadcastToPrivate(string $channel, string $event, ?callable $callback = null): void
    {
        $this->assertBroadcast("private-{$channel}", $event, $callback);
    }

    /**
     * Assert an event was broadcast to a presence channel
     */
    public function assertBroadcastToPresence(string $channel, string $event, ?callable $callback = null): void
    {
        $this->assertBroadcast("presence-{$channel}", $event, $callback);
    }

    /**
     * Assert nothing was broadcast
     */
    public function assertNothingBroadcast(): void
    {
        PHPUnit::assertCount(
            0,
            $this->broadcasts,
            'Failed asserting that no events were broadcast.'
        );
    }

    /**
     * Assert a notification was sent
     */
    public function assertNotificationSent(?callable $callback = null): void
    {
        $matching = collect($this->notifications);

        if ($callback) {
            $matching = $matching->filter($callback);
        }

        PHPUnit::assertTrue(
            $matching->count() > 0,
            'Failed asserting that a notification was sent.'
        );
    }

    /**
     * Assert no notifications were sent
     */
    public function assertNoNotificationsSent(): void
    {
        PHPUnit::assertCount(
            0,
            $this->notifications,
            'Failed asserting that no notifications were sent.'
        );
    }

    /**
     * Get all broadcasts
     */
    public function getBroadcasts(): array
    {
        return $this->broadcasts;
    }

    /**
     * Get all notifications
     */
    public function getNotifications(): array
    {
        return $this->notifications;
    }

    /**
     * Generate fake signature
     */
    public function generateSignature(string $payload): string
    {
        return hash_hmac('sha256', $payload, 'fake-secret');
    }

    /**
     * Verify signature (always returns true in fake)
     */
    public function verifySignature(string $payload, string $signature): bool
    {
        return true;
    }
}

class ChannelsFake
{
    public function all(): Collection
    {
        return collect([]);
    }

    public function find(string $id): ?array
    {
        return ['id' => $id, 'name' => 'fake-channel'];
    }

    public function create(array $data): array
    {
        return array_merge(['id' => 'fake-id'], $data);
    }

    public function update(string $id, array $data): array
    {
        return array_merge(['id' => $id], $data);
    }

    public function delete(string $id): bool
    {
        return true;
    }

    public function stats(string $id): array
    {
        return ['connections' => 0, 'events' => 0];
    }
}

class WebhooksFake
{
    public function all(): Collection
    {
        return collect([]);
    }

    public function find(string $id): ?array
    {
        return ['id' => $id, 'name' => 'fake-webhook'];
    }

    public function create(array $data): array
    {
        return array_merge(['id' => 'fake-id'], $data);
    }

    public function update(string $id, array $data): array
    {
        return array_merge(['id' => $id], $data);
    }

    public function delete(string $id): bool
    {
        return true;
    }
}

class NotificationBuilderFake
{
    protected array $notification = [];

    public function __construct(protected LiveWaveFake $fake) {}

    public function title(string $title): self
    {
        $this->notification['title'] = $title;
        return $this;
    }

    public function body(string $body): self
    {
        $this->notification['body'] = $body;
        return $this;
    }

    public function data(array $data): self
    {
        $this->notification['data'] = $data;
        return $this;
    }

    public function user(int $userId): self
    {
        $this->notification['user_id'] = $userId;
        return $this;
    }

    public function users(array $userIds): self
    {
        $this->notification['user_ids'] = $userIds;
        return $this;
    }

    public function channel(string $channel): self
    {
        $this->notification['channel'] = $channel;
        return $this;
    }

    public function type(string $type): self
    {
        $this->notification['type'] = $type;
        return $this;
    }

    public function success(): self
    {
        return $this->type('success');
    }

    public function warning(): self
    {
        return $this->type('warning');
    }

    public function error(): self
    {
        return $this->type('error');
    }

    public function send(): bool
    {
        $this->fake->recordNotification($this->notification);
        return true;
    }
}
