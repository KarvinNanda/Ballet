<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * Proxies come from TRUSTED_PROXIES (comma separated IPs/CIDRs, or "*").
     * Empty = trust none, so a client cannot fake its IP with X-Forwarded-For.
     */
    protected function proxies()
    {
        $value = trim((string) config('app.trusted_proxies'));

        if ($value === '') {
            return null;
        }

        return $value === '*' ? '*' : array_map('trim', explode(',', $value));
    }

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}
