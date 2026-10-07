<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/** AJAX endpoint behind the class dropdown on Add Transaction. Option text is "<class name> - <teacher>". */
class GetPriceTest extends TestCase
{
    use RefreshDatabase;

    public static function endpoints(): array
    {
        return [['admin', 'admin.transaction.price'], ['head', 'head.transaction.price']];
    }

    #[DataProvider('endpoints')]
    public function test_price_of_the_selected_class(string $role, string $name): void
    {
        $course = DB::table('class_types')->orderBy('id')->first();

        $this->actingAs(User::where('role', $role)->firstOrFail())
            ->get(route($name, ['text' => $course->class_name.' - Teacher']))
            ->assertOk()
            ->assertSeeText((string) $course->class_price);
    }

    #[DataProvider('endpoints')]
    public function test_missing_text_is_a_422_not_a_500(string $role, string $name): void
    {
        $this->actingAs(User::where('role', $role)->firstOrFail())
            ->get(route($name))
            ->assertStatus(422)
            ->assertJsonStructure(['message']);
    }

    #[DataProvider('endpoints')]
    public function test_array_text_is_a_422_not_a_500(string $role, string $name): void
    {
        $this->actingAs(User::where('role', $role)->firstOrFail())
            ->get(route($name, ['text' => ['x']]))
            ->assertStatus(422);
    }
}
