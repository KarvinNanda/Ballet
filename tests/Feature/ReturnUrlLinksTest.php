<?php

namespace Tests\Feature;

use App\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** After a failed save the form reopens with old('return_url'); Cancel and the hidden field must both follow it, and never echo a foreign value. */
class ReturnUrlLinksTest extends TestCase
{
    use RefreshDatabase;

    private function asHead(): static
    {
        return $this->actingAs(User::where('role', 'head')->firstOrFail());
    }

    public function test_cancel_and_hidden_field_both_carry_the_list_url_after_a_failed_save(): void
    {
        $classId = DB::table('class_transactions')->where('is_freeze', 1)->value('id');
        $listUrl = route('head.class.freeze.index', ['search' => 'x']);
        $editUrl = route('head.class.freeze.edit', $classId);

        $this->asHead()->from($listUrl)->get($editUrl)->assertOk();
        $this->from($editUrl)->post(route('head.class.freeze.update', $classId), ['inputPrice' => 'abc', 'return_url' => $listUrl])
            ->assertSessionHasErrors('inputPrice')->assertRedirect($editUrl);

        $html = $this->from($editUrl)->get($editUrl)->assertOk()->getContent();

        $this->assertStringContainsString('name="return_url" value="'.e($listUrl).'"', $html);
        $this->assertStringContainsString('<a href="'.e($listUrl).'" class="btn btn-outline-secondary">Cancel</a>', $html);
    }

    public function test_a_javascript_old_return_url_renders_the_fallback_never_javascript(): void
    {
        $classId = DB::table('class_transactions')->where('is_freeze', 1)->value('id');

        $html = $this->asHead()->withSession(['_old_input' => ['return_url' => 'javascript:alert(1)']])
            ->get(route('head.class.freeze.edit', $classId))->assertOk()->getContent();

        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringContainsString('<a href="'.e(url('/')).'" class="btn btn-outline-secondary">Cancel</a>', $html);
        $this->assertStringContainsString('name="return_url" value="'.e(url('/')).'"', $html);
    }

    public function test_a_foreign_old_return_url_on_the_stock_form_renders_the_fallback(): void
    {
        $stock = Stock::firstOrFail();

        $html = $this->asHead()->withSession(['_old_input' => ['return_url' => 'https://evil.example/x']])
            ->get(route('head.stock.edit', $stock))->assertOk()->getContent();

        $this->assertStringNotContainsString('evil.example', $html);
        $this->assertStringContainsString('<input type="hidden" name="return_url" value="'.e(url('/')).'">', $html);
    }
}
