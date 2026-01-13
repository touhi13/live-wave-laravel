<?php

namespace LiveWave\Facades;

use Illuminate\Support\Facades\Facade;
use LiveWave\Api\Channels;
use LiveWave\Api\Webhooks;
use LiveWave\Notifications\NotificationBuilder;

/**
 * @method static bool broadcast(string $channel, string $event, array $data = [])
 * @method static bool broadcastToPrivate(string $channel, string $event, array $data = [])
 * @method static bool broadcastToPresence(string $channel, string $event, array $data = [])
 * @method static bool broadcastToMany(array $channels, string $event, array $data = [])
 * @method static Channels channels()
 * @method static Webhooks webhooks()
 * @method static NotificationBuilder notify()
 * @method static string generateSignature(string $payload)
 * @method static bool verifySignature(string $payload, string $signature)
 *
 * @see \LiveWave\LiveWaveClient
 */
class LiveWave extends Facade
{
    /**
     * Indicates if the resolved instance should be cached.
     *
     * @var bool
     */
    protected static $cached = true;

    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'livewave';
    }

    /**
     * Fake all LiveWave calls for testing
     */
    public static function fake(): LiveWaveFake
    {
        static::swap($fake = new LiveWaveFake());

        return $fake;
    }
}
