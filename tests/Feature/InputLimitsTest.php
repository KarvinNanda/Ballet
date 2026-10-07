<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** With MySQL strict mode, too-long text or out-of-range numbers must be validation errors, not 500s. */
class InputLimitsTest extends TestCase
{
    use RefreshDatabase;

    public function test_too_long_teacher_address_is_a_validation_error(): void
    {
        $this->actingAs($this->headUser())->from(route('head.teacher.create'))
            ->post(route('head.teacher.store'), [
                'inputName' => 'Guru Baru', 'inputEmail' => 'guru.baru@example.com', 'inputDate_of_Birth' => '1995-01-01',
                'inputAddress' => str_repeat('a', 300), 'inputBonus' => 30, 'inputPhone' => '081234567890',
            ])
            ->assertSessionHasErrors('inputAddress');
    }

    public function test_out_of_range_stock_quantity_is_a_validation_error(): void
    {
        $this->actingAs($this->headUser())->from(route('head.stock.create'))
            ->post(route('head.stock.store'), ['inputName' => 'Leotard', 'inputSize' => 'M', 'inputQty' => '99999999999'])
            ->assertSessionHasErrors('inputQty');
    }

    public function test_out_of_range_class_price_is_a_validation_error(): void
    {
        $frozen = DB::table('class_transactions')->where('is_freeze', 1)->value('id');

        $this->actingAs($this->headUser())->from(route('head.class.freeze.edit', $frozen))
            ->post(route('head.class.freeze.update', $frozen), ['inputPrice' => '99999999999', 'return_url' => '/'])
            ->assertSessionHasErrors('inputPrice');
    }

    private function headUser(): User
    {
        return User::where('role', 'head')->firstOrFail();
    }
}
