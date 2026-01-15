# LiveWave Laravel SDK

**Optional** Laravel SDK for [LiveWave](https://github.com/touhi13/live-wave) - A self-hosted real-time WebSocket server for Laravel applications.

> **Note:** For basic broadcasting, you **don't need this SDK**! Just use standard Laravel Pusher configuration pointing to your LiveWave server. This SDK is only needed for advanced features like API management, webhooks, and notifications.

## Architecture

LiveWave works like [Laravel Reverb's multi-app feature](https://laravel.com/docs/12.x/reverb#additional-applications) - your LiveWave server acts as a **central WebSocket server** that multiple Laravel applications can connect to:

```
┌─────────────────────────────────────────────────────────┐
│              LiveWave Server (Reverb)                   │
│         Central WebSocket Server for All Apps           │
├─────────────────────────────────────────────────────────┤
│  Team 1 (E-commerce)  │  Team 2 (CRM)  │  Team 3       │
│  app_id: xxx          │  app_id: yyy    │  app_id: zzz   │
│  app_key              │  app_key        │  app_key       │
│  app_secret           │  app_secret     │  app_secret    │
└─────────────────────────────────────────────────────────┘
         ▲                    ▲                    ▲
         │                    │                    │
    ┌────┴───┐          ┌────┴───┐          ┌────┴───┐
    │ App 1  │          │ App 2  │          │ App 3  │
    │ Laravel│          │ Laravel│          │ Laravel│
    │(Pusher)│          │(Pusher)│          │(Pusher)│
    └────────┘          └────────┘          └────────┘
```

Each Laravel application connects to the **same LiveWave server** but with its own credentials (from their Team dashboard), keeping channels isolated.

## Basic Usage (No SDK Required)

### Step 1: Get Credentials from LiveWave Dashboard

When you create a Team in LiveWave, you get:

- `app_id`
- `app_key`
- `app_secret`

### Step 2: Configure Your Laravel App

**`.env` file:**

```env
BROADCAST_CONNECTION=pusher

PUSHER_APP_ID=your-app-id-from-livewave
PUSHER_APP_KEY=your-app-key-from-livewave
PUSHER_APP_SECRET=your-app-secret-from-livewave
PUSHER_HOST=your-livewave-server.com
PUSHER_PORT=8080
PUSHER_SCHEME=http
```

**`config/broadcasting.php`:**

```php
'pusher' => [
    'driver' => 'pusher',
    'key' => env('PUSHER_APP_KEY'),
    'secret' => env('PUSHER_APP_SECRET'),
    'app_id' => env('PUSHER_APP_ID'),
    'options' => [
        'host' => env('PUSHER_HOST'),
        'port' => env('PUSHER_PORT', 8080),
        'scheme' => env('PUSHER_SCHEME', 'http'),
        'useTLS' => false,
    ],
],
```

### Step 3: Frontend (Laravel Echo)

```bash
npm install laravel-echo pusher-js
```

```javascript
import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.Pusher = Pusher;

window.Echo = new Echo({
  broadcaster: "pusher",
  key: import.meta.env.VITE_PUSHER_APP_KEY,
  wsHost: import.meta.env.VITE_PUSHER_HOST,
  wsPort: import.meta.env.VITE_PUSHER_PORT,
  forceTLS: false,
  disableStats: true,
  enabledTransports: ["ws", "wss"],
});
```

**`.env`:**

```env
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
```

### Step 4: Broadcast Events

```php
// app/Events/MessageSent.php
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MessageSent implements ShouldBroadcast
{
    use InteractsWithSockets;

    public function __construct(public string $message) {}

    public function broadcastOn(): Channel
    {
        return new Channel('chat');
    }
}

// Dispatch
event(new MessageSent('Hello World!'));
```

**That's it!** No SDK needed for basic broadcasting. ✅

---

## When to Use This SDK

This SDK is **optional** and only needed for:

1. **API Management** - Create/manage channels, API keys via code
2. **Webhooks** - Receive events from LiveWave (channel created, member joined, etc.)
3. **Notifications** - Send rich notifications via API
4. **Convenience** - Helper methods and facade for common operations

## Installation (For Advanced Features)

```bash
composer require livewave/laravel-sdk
```

Then run the installation command:

```bash
php artisan livewave:install
```

This will:

- Publish the configuration file
- Update your `.env` with LiveWave credentials
- Configure the broadcasting driver

## SDK Configuration

### Environment Variables

Add these to your `.env` file:

```env
LIVEWAVE_APP_ID=your-app-id
LIVEWAVE_APP_KEY=your-app-key
LIVEWAVE_APP_SECRET=your-app-secret
LIVEWAVE_HOST=your-livewave-server.com
LIVEWAVE_PORT=8080
```

For production with SSL:

```env
LIVEWAVE_HOST=livewave.yourapp.com
LIVEWAVE_PORT=443
LIVEWAVE_USE_TLS=true
LIVEWAVE_SCHEME=https
LIVEWAVE_WS_SCHEME=wss
```

### Broadcasting Configuration

Add the `livewave` connection to `config/broadcasting.php`:

```php
'connections' => [
    'livewave' => [
        'driver' => 'livewave',
    ],
    // ... other connections
],
```

## SDK Usage Examples

### Direct Broadcasting (Using Facade)

```php
use LiveWave\Facades\LiveWave;

// Broadcast to a public channel
LiveWave::broadcast('news', 'article.published', [
    'title' => 'Breaking News',
    'content' => 'Something happened...',
]);

// Broadcast to a private channel
LiveWave::broadcastToPrivate('user.123', 'notification', [
    'message' => 'You have a new message',
]);

// Broadcast to multiple channels
LiveWave::broadcastToMany(['channel1', 'channel2'], 'event.name', $data);
```

### Channel Information

```php
// Get channel info
$info = LiveWave::getChannelInfo('presence-room.1', ['user_count', 'subscription_count']);

// Get all channels
$channels = LiveWave::getChannels('presence-', ['user_count']);

// Get presence channel users
$users = LiveWave::getPresenceUsers('presence-room.1');
```

### Webhooks

Create a route for webhooks:

```php
Route::post('/webhooks/livewave', [WebhookController::class, 'handle'])
    ->middleware('livewave.webhook');
```

The `livewave.webhook` middleware automatically verifies the webhook signature.

```php
class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        $event = $request->input('event');
        $data = $request->input('data');

        match($event) {
            'channel.created' => $this->handleChannelCreated($data),
            'channel.deleted' => $this->handleChannelDeleted($data),
            'member.added' => $this->handleMemberAdded($data),
            'member.removed' => $this->handleMemberRemoved($data),
            default => null,
        };

        return response()->json(['status' => 'ok']);
    }
}
```

## Private & Presence Channels

### Private Channels

```php
use Illuminate\Broadcasting\PrivateChannel;

public function broadcastOn(): PrivateChannel
{
    return new PrivateChannel('user.' . $this->userId);
}
```

Define authorization in `routes/channels.php`:

```php
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
```

### Presence Channels

```php
use Illuminate\Broadcasting\PresenceChannel;

public function broadcastOn(): PresenceChannel
{
    return new PresenceChannel('room.' . $this->roomId);
}
```

Authorization with user info:

```php
Broadcast::channel('room.{roomId}', function ($user, $roomId) {
    if ($user->canJoinRoom($roomId)) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'avatar' => $user->avatar_url,
        ];
    }
});
```

### Frontend Listening

```javascript
// Public channel
Echo.channel("chat").listen(".message.sent", (e) => {
  console.log("New message:", e.message);
});

// Private channel
Echo.private("user." + userId).listen(".notification", (e) => {
  console.log("Notification:", e);
});

// Presence channel
Echo.join("room." + roomId)
  .here((users) => {
    console.log("Users in room:", users);
  })
  .joining((user) => {
    console.log("User joined:", user);
  })
  .leaving((user) => {
    console.log("User left:", user);
  })
  .listen(".message", (e) => {
    console.log("Message:", e);
  });
```

## Testing

Use the `LiveWaveFake` for testing:

```php
use LiveWave\Testing\LiveWaveFake;
use LiveWave\Facades\LiveWave;

public function test_message_is_broadcast()
{
    LiveWave::fake();

    // Perform action that broadcasts
    $this->post('/messages', ['content' => 'Hello']);

    // Assert broadcast was called
    LiveWave::assertBroadcast('chat', 'message.sent', function ($data) {
        return $data['content'] === 'Hello';
    });
}
```

## Configuration Options

| Option               | Description                  | Default     |
| -------------------- | ---------------------------- | ----------- |
| `app_id`             | Your LiveWave application ID | -           |
| `app_key`            | Public key for Echo          | -           |
| `app_secret`         | Secret for signing           | -           |
| `server.host`        | LiveWave server host         | `127.0.0.1` |
| `server.port`        | LiveWave server port         | `8080`      |
| `server.scheme`      | HTTP scheme                  | `http`      |
| `options.use_tls`    | Enable TLS                   | `false`     |
| `options.verify_ssl` | Verify SSL certificates      | `true`      |

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for recent changes.

## License

MIT License. See [LICENSE](LICENSE) for details.
