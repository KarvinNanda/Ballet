<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/** return_url comes from a hidden form field, so it must never send the user to another site. */
class ReturnUrlRedirectTest extends TestCase
{
    use RefreshDatabase;

    public static function foreignUrls(): array
    {
        return [
            'absolute' => ['https://evil.example/login'],
            'protocol relative' => ['//evil.example/login'],
            'backslash trick' => ['/\\evil.example'],
            'javascript' => ['javascript:alert(1)'],
        ];
    }

    #[DataProvider('foreignUrls')]
    public function test_foreign_return_url_falls_back_to_home(string $returnUrl): void
    {
        $this->updateFrozenClassPrice($returnUrl)->assertRedirect(url('/'));
    }

    public function test_own_return_url_is_followed(): void
    {
        $back = route('headClassFreezeView');

        $this->updateFrozenClassPrice($back)->assertRedirect($back);
    }

    private function updateFrozenClassPrice(string $returnUrl)
    {
        $classId = DB::table('class_transactions')->where('is_freeze', 1)->value('id');

        return $this->actingAs(User::where('role', 'head')->firstOrFail())
            ->post(route('headUpdateClassFreeze', $classId), ['inputPrice' => 500000, 'return_url' => $returnUrl]);
    }
}
