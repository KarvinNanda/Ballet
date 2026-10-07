<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/** staff_prefix() reads the current route name. Probe routes, no database. */
class StaffRouteHelperTest extends TestCase
{
    public static function names(): array
    {
        return [
            'head page' => ['head.student.index', 'head'],
            'admin action' => ['admin.transaction.update', 'admin'],
            'admin dashboard (no dot)' => ['admin', 'admin'],
            'teacher area' => ['teacher.class.index', null],
            'look-alike prefix' => ['administrator.page', null],
        ];
    }

    #[DataProvider('names')]
    public function test_prefix_comes_from_the_first_segment_of_the_route_name(string $name, ?string $expected): void
    {
        Route::get('/__probe', fn () => staff_prefix())->name($name);
        Route::getRoutes()->refreshNameLookups();

        if ($expected === null) {
            $this->withoutExceptionHandling();
            $this->expectException(\LogicException::class);
        }

        $this->get('/__probe')->assertOk()->assertSee($expected);
    }

    public function test_a_route_without_a_name_fails_loudly(): void
    {
        Route::get('/__probe', fn () => staff_prefix());

        $this->withoutExceptionHandling();
        $this->expectException(\LogicException::class);
        $this->get('/__probe');
    }
}
