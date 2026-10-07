<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RouteNamesTest extends TestCase
{
    /** Duplicate names make `php artisan route:cache` fail during deploy. */
    public function test_route_names_are_unique(): void
    {
        $duplicates = collect(Route::getRoutes()->getRoutes())
            ->map->getName()
            ->filter()
            ->countBy()
            ->filter(fn ($count) => $count > 1)
            ->keys()
            ->all();

        $this->assertSame([], $duplicates);
    }
}
