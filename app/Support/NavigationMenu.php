<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Turns config/navigation.php into what the sidebar and bottom nav render:
 * resolved URLs, one active item per page, and which groups start open.
 */
class NavigationMenu
{
    public function __construct(private array $groups)
    {
    }

    public static function fromConfig(?string $role): self
    {
        return new self($role ? config("navigation.{$role}", []) : []);
    }

    /**
     * @param string $path current request path without leading slash, e.g. "admin/detail/class/5"
     * @return list<array{title: ?string, open: bool, items: list<array{label: string, url: string, icon: string, bottom: bool, active: bool}>}>
     */
    public function build(string $path, int $userId): array
    {
        $activeRoute = $this->activeRoute($path);

        return array_map(function (array $group) use ($activeRoute, $userId) {
            $items = array_map(fn (array $item) => [
                'label' => $item['label'],
                'url' => route($item['route'], isset($item['user_param']) ? [$item['user_param'] => $userId] : []),
                'icon' => $item['icon'],
                'bottom' => $item['bottom'] ?? false,
                'active' => $item['route'] === $activeRoute,
            ], $group['items']);

            return [
                'title' => $group['title'],
                'open' => in_array(true, array_column($items, 'active'), true),
                'items' => $items,
            ];
        }, $this->groups);
    }

    /** The item whose most specific pattern matches the path wins, so "admin/class/type" beats "admin/class/*". */
    private function activeRoute(string $path): ?string
    {
        $best = null;
        $bestLength = -1;

        foreach ($this->groups as $group) {
            foreach ($group['items'] as $item) {
                foreach ($item['active'] as $pattern) {
                    $length = strlen(rtrim($pattern, '*'));
                    if ($length > $bestLength && Str::is($pattern, $path)) {
                        $best = $item['route'];
                        $bestLength = $length;
                    }
                }
            }
        }

        return $best;
    }
}
