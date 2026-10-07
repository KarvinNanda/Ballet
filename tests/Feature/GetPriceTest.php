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
        return [['admin', '/admin/transaction/get-price'], ['head', '/head/transaction/get-price']];
    }

    #[DataProvider('endpoints')]
    public function test_price_of_the_selected_class(string $role, string $url): void
    {
        $course = DB::table('class_types')->orderBy('id')->first();

        $this->actingAs(User::where('role', $role)->firstOrFail())
            ->get($url.'?text='.urlencode($course->class_name.' - Teacher'))
            ->assertOk()
            ->assertSeeText((string) $course->class_price);
    }

    #[DataProvider('endpoints')]
    public function test_missing_text_is_a_422_not_a_500(string $role, string $url): void
    {
        $this->actingAs(User::where('role', $role)->firstOrFail())
            ->get($url)
            ->assertStatus(422)
            ->assertJsonStructure(['message']);
    }
}
