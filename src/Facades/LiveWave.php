<?php

namespace LiveWave\Facades;

use Illuminate\Support\Facades\Facade;
use LiveWave\Api\Channels;
use LiveWave\Api\Webhooks;
use LiveWave\Notifications\NotificationBuilder;

/**
 * @method static bool trigger(string|array $channels, string $event, array $data = [], ?string $socketId = null)
 * @method static bool broadcast(string $channel, string $event, array $data = [])
 * @method static bool broadcastToPrivate(string $channel, string $event, array $data = [])
 * @method static bool broadcastToPresence(string $channel, string $event, array $data = [])
 * @method static bool broadcastToMany(array $channels, string $event, array $data = [])
 * @method static array authorizeChannel(string $socketId, string $channelName)
 * @method static array authorizePresenceChannel(string $socketId, string $channelName, string $userId, array $userInfo = [])
 * @method static array|null getChannelInfo(string $channelName, array $info = [])
 * @method static array|null getChannels(string $prefix = '', array $info = [])
 * @method static array|null getPresenceUsers(string $channelName)
 * @method static Channels channels()
 * @method static Webhooks webhooks()
 * @method static NotificationBuilder notify()
 * @method static array getEchoConfig()
 * @method static string getAppId()
 * @method static string getAppKey()
 * @method static bool verifyWebhookSignature(string $payload, string $signature)
 *
 * @see \LiveWave\LiveWaveClient
 */
class LiveWave extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'livewave';
    }
}
