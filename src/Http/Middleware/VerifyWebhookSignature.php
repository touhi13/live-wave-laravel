<?php

namespace LiveWave\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use LiveWave\Facades\LiveWave;
use Symfony\Component\HttpFoundation\Response;

class VerifyWebhookSignature
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $signature = $request->header('X-LiveWave-Signature');

        if (!$signature) {
            abort(401, 'Missing webhook signature');
        }

        $payload = $request->getContent();

        if (!LiveWave::verifySignature($payload, $signature)) {
            abort(401, 'Invalid webhook signature');
        }

        // Check timestamp to prevent replay attacks
        $timestamp = $request->header('X-LiveWave-Timestamp');
        if ($timestamp) {
            $requestTime = strtotime($timestamp);
            $tolerance = 300; // 5 minutes

            if (abs(time() - $requestTime) > $tolerance) {
                abort(401, 'Webhook timestamp expired');
            }
        }

        return $next($request);
    }
}
