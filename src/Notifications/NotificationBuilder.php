<?php

namespace LiveWave\Notifications;

use Illuminate\Http\Client\PendingRequest;

class NotificationBuilder
{
    protected ?string $title = null;
    protected ?string $body = null;
    protected array $data = [];
    protected ?int $userId = null;
    protected array $userIds = [];
    protected ?string $channel = null;
    protected ?string $type = 'info';
    protected ?string $icon = null;
    protected ?string $actionUrl = null;
    protected ?string $actionText = null;

    public function __construct(
        protected PendingRequest $http
    ) {}

    /**
     * Set the notification title
     */
    public function title(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Set the notification body
     */
    public function body(string $body): self
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Set additional data
     */
    public function data(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Set target user
     */
    public function user(int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * Set target users
     */
    public function users(array $userIds): self
    {
        $this->userIds = $userIds;
        return $this;
    }

    /**
     * Set target channel
     */
    public function channel(string $channel): self
    {
        $this->channel = $channel;
        return $this;
    }

    /**
     * Set notification type (info, success, warning, error)
     */
    public function type(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Set notification as success type
     */
    public function success(): self
    {
        return $this->type('success');
    }

    /**
     * Set notification as warning type
     */
    public function warning(): self
    {
        return $this->type('warning');
    }

    /**
     * Set notification as error type
     */
    public function error(): self
    {
        return $this->type('error');
    }

    /**
     * Set notification icon
     */
    public function icon(string $icon): self
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * Set action button
     */
    public function action(string $text, string $url): self
    {
        $this->actionText = $text;
        $this->actionUrl = $url;
        return $this;
    }

    /**
     * Send the notification
     */
    public function send(): bool
    {
        $payload = [
            'title' => $this->title,
            'body' => $this->body,
            'type' => $this->type,
            'data' => $this->data,
        ];

        if ($this->icon) {
            $payload['icon'] = $this->icon;
        }

        if ($this->actionUrl && $this->actionText) {
            $payload['action'] = [
                'text' => $this->actionText,
                'url' => $this->actionUrl,
            ];
        }

        // Send to specific user
        if ($this->userId) {
            $payload['user_id'] = $this->userId;
            return $this->http->post('/api/notifications', $payload)->successful();
        }

        // Send to multiple users
        if (!empty($this->userIds)) {
            $payload['user_ids'] = $this->userIds;
            return $this->http->post('/api/notifications/batch', $payload)->successful();
        }

        // Send to channel
        if ($this->channel) {
            $payload['channel'] = $this->channel;
            return $this->http->post('/api/notifications/channel', $payload)->successful();
        }

        throw new \InvalidArgumentException('No recipient specified. Use user(), users(), or channel().');
    }
}
