<?php

namespace LiveWave\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use LiveWave\LiveWaveClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class VerifyWebhookSignature
{
    public function __construct(
        protected LiveWaveClient $client
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $signature = $request->header('X-LiveWave-Signature');

        if (!$signature) {
            throw new AccessDeniedHttpException('Missing webhook signature.');
        }

        $payload = $request->getContent();
        $timestamp = $request->header('X-LiveWave-Timestamp');

        // Verify timestamp to prevent replay attacks
        if ($timestamp) {
            $tolerance = config('livewave.webhooks.tolerance', 300);

            if (abs(time() - (int) $timestamp) > $tolerance) {
                throw new AccessDeniedHttpException('Webhook timestamp expired.');
            }

            // Include timestamp in signature verification
            $payload = $timestamp . '.' . $payload;
        }

        if (!$this->client->verifyWebhookSignature($payload, $signature)) {
            throw new AccessDeniedHttpException('Invalid webhook signature.');
        }

        return $next($request);
    }
}
