<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PageSmokeTest extends TestCase
{
    use RefreshDatabase;

    private const ROLES = ['admin', 'head', 'teacher', 'finance'];

    private const SHARED_PAGES = ['profile', 'password'];

    /** GET routes that change data or stream files. Never call them in a smoke test. */
    private const SKIP_PATTERN = '/logout|delete|destroy|reset|freeze|print|export|download/i';

    /** Pages allowed to fail. Must stay empty: every page renders. Entries are "role /uri". */
    private const KNOWN_BROKEN = [];

    public function test_every_parameterless_get_page_renders_for_its_role(): void
    {
        $failures = [];

        foreach (self::ROLES as $role) {
            $user = User::where('role', $role)->firstOrFail();

            foreach ($this->pageUris($role) as $uri) {
                if (in_array("{$role} {$uri}", self::KNOWN_BROKEN, true)) {
                    continue;
                }

                $status = $this->actingAs($user)->get($uri)->status();

                if ($status >= 500) {
                    $failures[] = "{$role} {$uri}";
                }
            }
        }

        $this->assertSame([], $failures, 'Pages returned HTTP 5xx');
    }

    /** @return list<string> */
    private function pageUris(string $role): array
    {
        return collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($route) => in_array('GET', $route->methods(), true))
            ->filter(fn ($route) => ! str_contains($route->uri(), '{'))
            ->filter(fn ($route) => $route->uri() === $role
                || str_starts_with($route->uri(), "{$role}/")
                || in_array($route->uri(), self::SHARED_PAGES, true))
            ->reject(fn ($route) => preg_match(self::SKIP_PATTERN, $route->uri().' '.$route->getName()) === 1)
            ->map(fn ($route) => '/'.$route->uri())
            ->values()
            ->all();
    }
}
