<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Only the gateway may call this service. Every request must carry the
 * short-lived RS256 JWT the gateway signs; its claims are kept on the
 * request as the "gateway_claims" attribute.
 */
class VerifyGatewayToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if ($token === null) {
            return $this->reject('Missing gateway token.');
        }

        try {
            JWT::$leeway = (int) config('famexus.jwt.leeway');
            $claims = JWT::decode($token, new Key($this->publicKey(), 'RS256'));
        } catch (Throwable) {
            return $this->reject('Invalid gateway token.');
        }

        if (($claims->iss ?? null) !== config('famexus.jwt.issuer')
            || ($claims->aud ?? null) !== config('famexus.jwt.audience')) {
            return $this->reject('Invalid gateway token.');
        }

        $request->attributes->set('gateway_claims', $claims);

        return $next($request);
    }

    private function publicKey(): string
    {
        $path = (string) config('famexus.jwt.public_key');

        return (string) file_get_contents(str_starts_with($path, '/') ? $path : base_path($path));
    }

    private function reject(string $message): Response
    {
        return response()->json(['message' => $message], 401);
    }
}
