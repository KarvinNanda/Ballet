<?php

namespace Tests\Unit;

use App\Support\NavigationMenu;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/** Uses a hand-made menu instead of config/navigation.php; needs the app only for route(). No database. */
class NavigationMenuTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Route::get('/x/class', fn () => '')->name('unit.class');
        Route::get('/x/class/type', fn () => '')->name('unit.type');
        Route::get('/x/profile/{id}', fn () => '')->name('unit.profile');
        Route::getRoutes()->refreshNameLookups();
    }

    private function menu(): NavigationMenu
    {
        return new NavigationMenu([
            ['title' => 'Master', 'items' => [
                ['label' => 'Class', 'route' => 'unit.class', 'icon' => 'bi-a', 'active' => ['x/class', 'x/class/*']],
                ['label' => 'Course', 'route' => 'unit.type', 'icon' => 'bi-b', 'bottom' => true, 'active' => ['x/class/type*']],
            ]],
            ['title' => 'Me', 'items' => [
                ['label' => 'Profile', 'route' => 'unit.profile', 'icon' => 'bi-c', 'user_param' => 'id', 'active' => ['x/profile/*']],
            ]],
        ]);
    }

    /** @return array<string, bool> label => active */
    private function activeByLabel(array $groups): array
    {
        return collect($groups)->flatMap(fn ($g) => $g['items'])->mapWithKeys(fn ($i) => [$i['label'] => $i['active']])->all();
    }

    public function test_the_most_specific_pattern_wins(): void
    {
        $active = $this->activeByLabel($this->menu()->build('x/class/type', 1));

        $this->assertSame(['Class' => false, 'Course' => true, 'Profile' => false], $active);
    }

    public function test_a_sub_page_lights_its_parent_item(): void
    {
        $active = $this->activeByLabel($this->menu()->build('x/class/5', 1));

        $this->assertTrue($active['Class']);
        $this->assertFalse($active['Course']);
    }

    public function test_only_the_group_with_the_active_item_is_open(): void
    {
        $groups = $this->menu()->build('x/class', 1);

        $this->assertTrue($groups[0]['open']);
        $this->assertFalse($groups[1]['open']);
    }

    public function test_unknown_path_lights_nothing(): void
    {
        $groups = $this->menu()->build('x/elsewhere', 1);

        $this->assertNotContains(true, $this->activeByLabel($groups));
        $this->assertFalse($groups[0]['open']);
    }

    public function test_user_param_is_filled_with_the_logged_in_user_id(): void
    {
        $groups = $this->menu()->build('x/class', 42);

        $this->assertSame(url('/x/profile/42'), $groups[1]['items'][0]['url']);
    }

    public function test_bottom_defaults_to_false(): void
    {
        $items = $this->menu()->build('x/class', 1)[0]['items'];

        $this->assertFalse($items[0]['bottom']);
        $this->assertTrue($items[1]['bottom']);
    }
}
