<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/** Opening a page for a record that does not exist must answer 4xx, never a 500. */
class MissingRecordTest extends TestCase
{
    use RefreshDatabase;

    public function test_pages_with_an_unknown_id_do_not_crash(): void
    {
        $crashes = [];

        foreach (['admin', 'head', 'teacher', 'finance'] as $role) {
            $user = User::where('role', $role)->firstOrFail();

            foreach (Route::getRoutes()->getRoutes() as $route) {
                $uri = $route->uri();
                if (! in_array('GET', $route->methods(), true) || ! str_starts_with($uri, "{$role}/") || ! str_contains($uri, '{')) {
                    continue;
                }

                $path = '/'.preg_replace('/\{[^}]+\}/', '999999', $uri);
                $status = $this->actingAs($user)->get($path)->status();

                if ($status >= 500) {
                    $crashes[] = "{$role} {$path}";
                }
            }
        }

        $this->assertSame([], $crashes);
    }

    public static function sortLinks(): array
    {
        return [
            'admin stock' => ['admin', 'admin.stock.sort', ['name', 'asc']],
            'admin student' => ['admin', 'admin.student.sort', ['dob', 'desc']],
            'admin transaction' => ['admin', 'admin.transaction.sort', ['price', 'asc']],
            'admin class' => ['admin', 'admin.class.sort', ['class_name', 'desc']],
            'head stock' => ['head', 'head.stock.sort', ['quantity', 'asc']],
            'head student' => ['head', 'head.student.sort', ['age', 'asc']],
            'head transaction' => ['head', 'head.transaction.sort', ['payment_status', 'desc']],
            'head class' => ['head', 'head.class.sort', ['status', 'asc']],
            'finance stock' => ['finance', 'financeStockViewSorting', ['size', 'asc']],
            'finance transaction' => ['finance', 'financeTransactionSorting', ['price']],
        ];
    }

    /** The sort links the pages show keep working (column allowlist matches the views). One user per test. */
    #[DataProvider('sortLinks')]
    public function test_sort_links_used_by_the_pages_work(string $role, string $name, array $values): void
    {
        $params = Route::getRoutes()->getByName($name)->parameterNames();

        $this->actingAs(User::where('role', $role)->firstOrFail())
            ->get(route($name, array_combine($params, $values)))
            ->assertOk();
    }
}
