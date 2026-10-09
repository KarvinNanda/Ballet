<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/** own_url() decides which return_url may be echoed into an href or followed as a redirect. */
class OwnUrlTest extends TestCase
{
    public static function urls(): array
    {
        return [
            'relative path' => ['/admin/student?search=x', '/admin/student?search=x'],
            'same-host absolute' => ['http://localhost/head/stock?page=2', 'http://localhost/head/stock?page=2'],
            'other host' => ['https://evil.example/login', '/fallback'],
            'protocol relative' => ['//evil.example/login', '/fallback'],
            'backslash trick' => ['/\\evil.example', '/fallback'],
            'javascript scheme' => ['javascript:alert(1)', '/fallback'],
            'array' => [['/admin'], '/fallback'],
            'null' => [null, '/fallback'],
            'empty string' => ['', '/fallback'],
            'tab inside the leading slashes' => ["/\t/evil.example", '/fallback'],
            'leading space' => [' //evil.example', '/fallback'],
            'newline' => ["/admin\n", '/fallback'],
        ];
    }

    #[DataProvider('urls')]
    public function test_only_own_urls_pass(mixed $url, string $expected): void
    {
        config(['app.url' => 'http://localhost']);
        $this->app['url']->forceRootUrl('http://localhost');

        $this->assertSame($expected, own_url($url, '/fallback'));
    }
}
