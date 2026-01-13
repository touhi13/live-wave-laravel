<?php

namespace LiveWave\Broadcasting;

use Illuminate\Broadcasting\Broadcasters\Broadcaster;
use Illuminate\Broadcasting\BroadcastException;
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
        $channelName = $request->channel_name;

        // Remove prefixes to get the actual channel name
        $channelName = str_replace(['private-', 'presence-'], '', $channelName);

        // Find the channel callback
        $channel = $this->verifyUserCanAccessChannel(
            $request,
            $channelName
        );

        if (!$channel) {
            throw new AccessDeniedHttpException();
        }

        return parent::validAuthenticationResponse($request, $channel);
    }

    /**
     * Return the valid authentication response.
     */
    public function validAuthenticationResponse($request, $result)
    {
        if (is_bool($result)) {
            return json_encode($result);
        }

        $channelName = $request->channel_name;
        $socketId = $request->socket_id;

        // For presence channels, include user info
        if (str_starts_with($channelName, 'presence-')) {
            $user = $request->user();
            
            return [
                'auth' => $this->generateAuthSignature($socketId, $channelName),
                'channel_data' => json_encode([
                    'user_id' => $user->getAuthIdentifier(),
                    'user_info' => $result,
                ]),
            ];
        }

        return [
            'auth' => $this->generateAuthSignature($socketId, $channelName),
        ];
    }

    /**
     * Generate the auth signature
     */
    protected function generateAuthSignature(string $socketId, string $channelName): string
    {
        $stringToSign = "{$socketId}:{$channelName}";
        
        return $this->client->generateSignature($stringToSign);
    }

    /**
     * Broadcast the given event.
     */
    public function broadcast(array $channels, $event, array $payload = [])
    {
        $channelNames = collect($channels)->map(function ($channel) {
            return (string) $channel;
        })->toArray();

        try {
            if (count($channelNames) === 1) {
                $this->client->broadcast($channelNames[0], $event, $payload);
            } else {
                $this->client->broadcastToMany($channelNames, $event, $payload);
            }
        } catch (\Exception $e) {
            throw new BroadcastException(
                sprintf('LiveWave broadcast failed: %s', $e->getMessage())
            );
        }
    }
}
