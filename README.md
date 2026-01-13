# LiveWave Laravel SDK

Official Laravel SDK for LiveWave real-time events and notifications platform.

## Installation

```bash
composer require livewave/laravel-sdk
```

Or add manually to your `composer.json`:

```json
{
    "require": {
        "livewave/laravel-sdk": "^1.0"
    }
}
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=livewave-config
```

Add your credentials to `.env`:

```env
LIVEWAVE_API_KEY=your_api_key_here
LIVEWAVE_API_SECRET=your_api_secret_here
LIVEWAVE_BASE_URL=https://your-livewave-instance.com
```

## Quick Start

### Broadcasting Events

```php
use LiveWave\Facades\LiveWave;

// Broadcast to a public channel
LiveWave::broadcast('my-channel', 'my-event', [
    'message' => 'Hello, World!',
    'user_id' => 123,
]);

// Broadcast to a private channel
LiveWave::broadcastToPrivate('user.123', 'notification', [
    'title' => 'New Message',
    'body' => 'You have a new message',
]);

// Broadcast to a presence channel
LiveWave::broadcastToPresence('chat-room.1', 'user-typing', [
    'user' => 'John Doe',
]);
```

### Using the Facade

```php
use LiveWave\Facades\LiveWave;

// Get all channels
$channels = LiveWave::channels()->all();

// Create a channel
$channel = LiveWave::channels()->create([
    'name' => 'notifications',
    'type' => 'private',
]);

// Delete a channel
LiveWave::channels()->delete('channel-id');

// Get channel statistics
$stats = LiveWave::channels()->stats('channel-id');
```

### Sending Notifications

```php
use LiveWave\Facades\LiveWave;

// Send to a single user
LiveWave::notify()
    ->user(123)
    ->title('Welcome!')
    ->body('Thanks for signing up')
    ->data(['action' => 'welcome'])
    ->send();

// Send to multiple users
LiveWave::notify()
    ->users([1, 2, 3])
    ->title('Announcement')
    ->body('New feature available!')
    ->send();

// Send to a channel
LiveWave::notify()
    ->channel('announcements')
    ->title('System Update')
    ->body('Scheduled maintenance tonight')
    ->send();
```

### Event Broadcasting with Laravel Events

```php
use LiveWave\Broadcasting\LiveWaveChannel;

class OrderShipped implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Order $order) {}

    public function broadcastOn(): array
    {
        return [
            new LiveWaveChannel('orders.'.$this->order->user_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'order.shipped';
    }
}
```

## API Reference

### LiveWave Facade Methods

| Method | Description |
|--------|-------------|
| `broadcast($channel, $event, $data)` | Broadcast to a public channel |
| `broadcastToPrivate($channel, $event, $data)` | Broadcast to a private channel |
| `broadcastToPresence($channel, $event, $data)` | Broadcast to a presence channel |
| `channels()` | Access the Channels API |
| `notify()` | Create a notification builder |
| `webhooks()` | Access the Webhooks API |

### Channels API

```php
$channels = LiveWave::channels();

$channels->all();                    // List all channels
$channels->find($id);                // Get a specific channel
$channels->create($data);            // Create a channel
$channels->update($id, $data);       // Update a channel
$channels->delete($id);              // Delete a channel
$channels->stats($id);               // Get channel statistics
```

### Webhooks API

```php
$webhooks = LiveWave::webhooks();

$webhooks->all();                    // List all webhooks
$webhooks->find($id);                // Get a specific webhook
$webhooks->create($data);            // Create a webhook
$webhooks->update($id, $data);       // Update a webhook
$webhooks->delete($id);              // Delete a webhook
$webhooks->deliveries($id);          // Get delivery history
```

## Middleware

### Webhook Signature Verification

```php
// In your routes
Route::post('/webhooks/livewave', [WebhookController::class, 'handle'])
    ->middleware('livewave.webhook');

// In your controller
public function handle(Request $request)
{
    $payload = $request->all();
    $event = $request->header('X-LiveWave-Event');
    
    match($event) {
        'event.broadcasted' => $this->handleEventBroadcasted($payload),
        'channel.created' => $this->handleChannelCreated($payload),
        default => null,
    };
    
    return response()->json(['status' => 'ok']);
}
```

## Testing

```php
use LiveWave\Facades\LiveWave;

// Fake all LiveWave calls
LiveWave::fake();

// Assert an event was broadcast
LiveWave::assertBroadcast('my-channel', 'my-event');

// Assert with specific data
LiveWave::assertBroadcast('my-channel', 'my-event', function ($data) {
    return $data['message'] === 'Hello';
});

// Assert nothing was broadcast
LiveWave::assertNothingBroadcast();
```

## License

MIT License - see LICENSE file for details.
