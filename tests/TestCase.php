<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Seed once, when RefreshDatabase migrates. Seeding per test inside the rolled-back transaction
     * shifts AUTO_INCREMENT ids, and the seeders use hard-coded foreign ids.
     */
    protected $seed = true;
}
