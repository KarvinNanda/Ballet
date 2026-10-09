<?php

use Illuminate\Support\Facades\Route;

if (! function_exists('staff_prefix')) {
    /** "admin" or "head", read from the current route name ("admin", "admin.student.index", ...). */
    function staff_prefix(): string
    {
        $prefix = strtok((string) Route::currentRouteName(), '.');

        if (! in_array($prefix, ['admin', 'head'], true)) {
            throw new LogicException('staff_route() used outside an admin/head route: '.Route::currentRouteName());
        }

        return $prefix;
    }
}

if (! function_exists('staff_route')) {
    /** route() for the current staff area: staff_route('student.index') is admin.student.index or head.student.index. */
    function staff_route(string $name, mixed $parameters = [], bool $absolute = true): string
    {
        return route(staff_prefix().'.'.$name, $parameters, $absolute);
    }
}

if (! function_exists('own_url')) {
    /**
     * $url when it points inside this app, else $fallback. Used for every return_url (redirects and Back/Cancel links).
     * Rejects other hosts and ports, "//host", backslashes, non-http(s) schemes ("javascript:") and non-strings.
     */
    function own_url(mixed $url, string $fallback): string
    {
        // Browsers drop tabs/newlines inside URLs ("/\t/evil" becomes "//evil"), so control characters and spaces are refused.
        if (! is_string($url) || $url === '' || str_contains($url, '\\') || preg_match('/[\x00-\x20\x7F]/', $url)) {
            return $fallback;
        }

        if (str_starts_with($url, '/') && ! str_starts_with($url, '//')) {
            return $url;
        }

        $parts = parse_url($url);
        $app = parse_url(url('/'));

        $own = in_array($parts['scheme'] ?? null, ['http', 'https'], true)
            && ($parts['host'] ?? null) === ($app['host'] ?? null)
            && ($parts['port'] ?? null) === ($app['port'] ?? null);

        return $own ? $url : $fallback;
    }
}
