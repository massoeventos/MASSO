<?php

namespace Masso\Http\Middleware;

use Closure;

class EnsureDeviceToken
{
    /**
     * Ensure the browser has a persistent anonymous token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = $request->cookie('device_token');

        if (empty($token) || !is_string($token) || strlen($token) < 16) {
            $token = $this->generateDeviceToken();

            // Make it available in this request cycle
            $request->cookies->set('device_token', $token);

            $response = $next($request);

            // Persist for 1 year
            return $response->withCookie(cookie('device_token', $token, 525600, null, null, false, true));
        }

        return $next($request);
    }

    /**
     * Generate a reasonably unique, anonymous device token.
     *
     * Compatible with older Laravel/PHP versions (no Str::uuid()).
     *
     * @return string
     */
    protected function generateDeviceToken()
    {
        // Prefer cryptographically-strong randomness when available.
        if (function_exists('random_bytes')) {
            return bin2hex(random_bytes(16)); // 32 chars
        }

        if (function_exists('openssl_random_pseudo_bytes')) {
            $bytes = openssl_random_pseudo_bytes(16);
            if ($bytes !== false) {
                return bin2hex($bytes);
            }
        }

        // Fallback (not cryptographically strong, but stable enough for an anonymous identifier).
        return sha1(uniqid('device', true) . mt_rand());
    }
}
