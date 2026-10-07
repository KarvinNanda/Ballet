<?php

namespace Tests\Feature;

use Tests\TestCase;

/** Unauthenticated GET endpoints that wrote data were removed. */
class ApiSuitRoutesTest extends TestCase
{
    public function test_suit_endpoints_are_gone(): void
    {
        foreach (['transaction-suit', 'class-suit', 'age-suit', 'class-price-suit', 'class-price-suit-non-freeze'] as $path) {
            $this->get("/api/{$path}")->assertNotFound();
        }
    }
}
