<?php

namespace LiveWave\Broadcasting;

use Illuminate\Broadcasting\Broadcasters\Broadcaster;
use Illuminate\Broadcasting\BroadcastException;
use Illuminate\Http\Request;
use LiveWave\LiveWaveClient;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class LiveWaveBroadcaster extends Broadcaster
{
    public function __construct(
        protected LiveWaveClient $client
    ) {}

    /**
     * Authenticate the incoming request for a given channel.
     */
    public function auth($request)
    {
        $channelName = $this->normalizeChannelName($request->channel_name);

        if (
            str_starts_with($request->channel_name, 'private-') &&
            !$this->retrieveUser($request, $channelName)
        ) {
            throw new AccessDeniedHttpException('Unauthorized.');
        }

        return parent::verifyUserCanAccessChannel(
            $request,
            $channelName
        );
    }

    /**
     * Return the valid authentication response.
     */
    public function validAuthenticationResponse($request, $result)
    {
        $channelName = $request->channel_name;
        $socketId    = $request->socket_id;

        // For presence channels, include user info
        if (str_starts_with($channelName, 'presence-')) {
            $user = $this->retrieveUser($request, $channelName);

            $userId   = $user->getAuthIdentifier();
            $userInfo = is_array($result) ? $result : [];

            return $this->client->authorizePresenceChannel(
                $socketId,
                $channelName,
                (string) $userId,
                $userInfo
            );
        }

        return $this->client->authorizeChannel($socketId, $channelName);
    }

    /**
     * Normalize the channel name (remove private-/presence- prefix)
     */
    protected function normalizeChannelName(string $channelName): string
    {
        if (str_starts_with($channelName, 'private-')) {
            return substr($channelName, 8);
        }

        if (str_starts_with($channelName, 'presence-')) {
            return substr($channelName, 9);
        }

        return $channelName;
    }

    /**
     * Broadcast the given event.
     */
    public function broadcast(array $channels, $event, array $payload = [])
    {
        $socket = $payload['socket'] ?? null;

        $channelNames = collect($channels)->map(function ($channel) {
            return (string) $channel;
        })->toArray();

        try {
            $this->client->trigger($channelNames, $event, $payload, $socket);
        } catch (\Exception $e) {
            throw new BroadcastException(
                sprintf('LiveWave broadcast failed: %s', $e->getMessage())
            );
        }
    }
}
