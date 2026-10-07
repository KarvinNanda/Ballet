<?php

namespace Tests\Feature\Staff;

use Illuminate\Support\Facades\Route;

class StaffRouteHelperTest extends StaffTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Route::middleware('web')->get('/admin/__probe', fn () => staff_route('probe.target'))->name('admin.probe.source');
        Route::middleware('web')->get('/head/__probe', fn () => staff_route('probe.target'))->name('head.probe.source');
        Route::get('/admin/__target', fn () => 'ok')->name('admin.probe.target');
        Route::get('/head/__target', fn () => 'ok')->name('head.probe.target');
        Route::middleware('web')->get('/__outside', fn () => staff_route('probe.target'))->name('outside');
        Route::getRoutes()->refreshNameLookups();
    }

    public function test_it_uses_the_admin_prefix_on_an_admin_route(): void
    {
        $this->get('/admin/__probe')->assertSee(url('/admin/__target'));
    }

    public function test_it_uses_the_head_prefix_on_a_head_route(): void
    {
        $this->get('/head/__probe')->assertSee(url('/head/__target'));
    }

    public function test_it_fails_loudly_outside_a_staff_route(): void
    {
        $this->withoutExceptionHandling();
        $this->expectException(\LogicException::class);
        $this->get('/__outside');
    }
}
