<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Redirect to a return_url sent by a form, but only inside this app.
     * Anything else (other host, "//host", "/\host", "javascript:") goes to the home page.
     */
    protected function backTo(?string $url): RedirectResponse
    {
        return redirect()->to($this->isOwnUrl($url) ? $url : url('/'));
    }

    /**
     * Column and direction from a sort link (/sorting/{column}/{direction}).
     * Only columns the page offers, only asc/desc; anything else is 404 instead of an SQL error.
     *
     * @return array{0: string, 1: string}
     */
    protected function sortOrFail(string $column, ?string $direction, array $columns): array
    {
        $direction = strtolower($direction ?? 'asc');
        abort_unless(in_array($column, $columns, true) && in_array($direction, ['asc', 'desc'], true), 404);

        return [$column, $direction];
    }

    private function isOwnUrl(?string $url): bool
    {
        if ($url === null || $url === '' || str_contains($url, '\\')) {
            return false;
        }

        if (str_starts_with($url, '/') && ! str_starts_with($url, '//')) {
            return true;
        }

        $parts = parse_url($url);
        $app = parse_url(url('/'));

        return in_array($parts['scheme'] ?? null, ['http', 'https'], true)
            && ($parts['host'] ?? null) === ($app['host'] ?? null)
            && ($parts['port'] ?? null) === ($app['port'] ?? null);
    }
}
