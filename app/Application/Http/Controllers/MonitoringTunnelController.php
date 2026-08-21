<?php

namespace App\Application\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

class MonitoringTunnelController extends Controller
{
    /**
     * Relays the frontend's Sentry/Bugsink envelope server-side, so the browser never talks to
     * Bugsink directly (commonly blocked by ad-blockers matching the `/api/<id>/envelope/` pattern).
     */
    public function store(Request $request): Response
    {
        $dsn = config('sentry_tunnel.dsn');
        if ($dsn === '' || $dsn === null) {
            return response()->noContent();
        }

        $dsnParts = parse_url($dsn);
        $host = $dsnParts['host'] ?? null;
        $publicKey = $dsnParts['user'] ?? null;
        $projectId = isset($dsnParts['path']) ? ltrim($dsnParts['path'], '/') : null;

        if ($host === null || $publicKey === null || $projectId === null || $projectId === '') {
            return response()->noContent();
        }

        $scheme = $dsnParts['scheme'] ?? 'https';
        $upstreamUrl = "{$scheme}://{$host}/api/{$projectId}/envelope/?sentry_version=7&sentry_key={$publicKey}";

        $upstreamRequest = Http::withBody(
            $request->getContent(),
            $request->header('Content-Type', 'application/x-sentry-envelope'),
        )->timeout(5);

        $contentEncoding = $request->header('Content-Encoding');
        if (in_array($contentEncoding, ['gzip', 'br', 'deflate'], true)) {
            $upstreamRequest = $upstreamRequest->withHeaders(['Content-Encoding' => $contentEncoding]);
        }

        try {
            $upstreamResponse = $upstreamRequest->post($upstreamUrl);
        } catch (ConnectionException) {
            // Bugsink injoignable (indisponible, timeout, DNS...) : le SDK n'a pas besoin de le savoir.
            return response()->noContent();
        }

        return response(
            $upstreamResponse->body(),
            $upstreamResponse->status(),
            array_filter([
                'Content-Type' => 'application/json',
                'X-Sentry-Rate-Limits' => $upstreamResponse->header('X-Sentry-Rate-Limits'),
                'Retry-After' => $upstreamResponse->header('Retry-After'),
            ]),
        );
    }
}
