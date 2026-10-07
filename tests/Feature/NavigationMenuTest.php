<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use App\Support\NavigationMenu;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class NavigationMenuTest extends TestCase
{
    public static function activePages(): array
    {
        return [
            'class detail lights Class' => ['admin', 'admin/detail/class/5', 'Class', 'Master'],
            'course list lights Course, not Class' => ['admin', 'admin/class/type', 'Course', 'Master'],
            'freeze detail lights Class Freeze' => ['admin', 'admin/detail/class/freeze/3', 'Class Freeze', 'Master'],
            'transaction detail lights Transaction' => ['admin', 'admin/transaction/detail/9', 'Transaction', 'Operasional'],
            'head rule edit lights Rule' => ['head', 'head/report/rule/update/2', 'Rule & Regulation', 'Operasional'],
            'head class report lights Class Attendance' => ['head', 'head/report/class', 'Class Attendance', 'Report'],
            'teacher schedule lights Schedule, not Class' => ['teacher', 'teacher/view/class/schedule/3', 'Schedule', null],
            'finance stock in lights Stock' => ['finance', 'finance/in/4', 'Stock', 'Report'],
        ];
    }

    #[DataProvider('activePages')]
    public function test_exactly_one_item_is_active_and_its_group_is_open(string $role, string $path, string $label, ?string $group): void
    {
        $menu = NavigationMenu::fromConfig($role)->build($path, 7);

        $active = $this->activeItems($menu);
        $this->assertSame([$label], array_column($active, 'label'));

        foreach ($menu as $g) {
            $hasActive = collect($g['items'])->contains('active', true);
            $this->assertSame($hasActive, $g['open'], "group {$g['title']} open state");
        }
        $this->assertSame($group, collect($menu)->first(fn ($g) => $g['open'])['title'] ?? null);
    }

    public function test_user_param_is_filled_with_the_user_id(): void
    {
        $menu = NavigationMenu::fromConfig('teacher')->build('teacher', 7);

        $schedule = collect($menu)->flatMap(fn ($g) => $g['items'])->firstWhere('label', 'Schedule');
        $this->assertSame(route('viewAllScheduleTeacher', ['id' => 7]), $schedule['url']);
    }

    public function test_unknown_role_has_no_menu(): void
    {
        $this->assertSame([], NavigationMenu::fromConfig('buyer')->build('buyer', 1));
        $this->assertSame([], NavigationMenu::fromConfig(null)->build('', 1));
    }

    public function test_every_role_page_highlights_one_menu_item(): void
    {
        $missing = [];

        foreach (['admin', 'head', 'teacher', 'finance'] as $role) {
            $navigation = NavigationMenu::fromConfig($role);

            foreach (Route::getRoutes()->getRoutes() as $route) {
                $uri = $route->uri();
                $isRolePage = $uri === $role || str_starts_with($uri, "{$role}/");
                $isPage = in_array('GET', $route->methods(), true) && ! preg_match('/delete|get-price/i', $uri);

                if ($isRolePage && $isPage) {
                    $path = preg_replace('/\{[^}]+\}/', '1', $uri);
                    if (count($this->activeItems($navigation->build($path, 1))) !== 1) {
                        $missing[] = $path;
                    }
                }
            }
        }

        $this->assertSame([], $missing, 'Pages without exactly one highlighted menu item');
    }

    private function activeItems(array $menu): array
    {
        return collect($menu)->flatMap(fn ($g) => $g['items'])->where('active', true)->values()->all();
    }
}
