<?php

namespace Tests\Unit;

use App\Support\Discount;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DiscountTest extends TestCase
{
    public static function totals(): array
    {
        return [
            'null' => [null, 350000],
            'empty' => ['', 350000],
            'zero' => ['0', 350000],
            'amount' => ['50000', 300000],
            'percent' => ['10%', 315000],
            'full percent' => ['100%', 0],
            'amount equal to price' => ['350000', 0],
            'text' => ['abc', null],
            'over 100 percent' => ['101%', null],
            'negative' => ['-5', null],
            'amount over price' => ['400000', null],
            'decimal' => ['5.5', null],
        ];
    }

    #[DataProvider('totals')]
    public function test_total(?string $discount, ?int $expected): void
    {
        $this->assertSame($expected, Discount::total(350000, $discount));
    }

    public function test_total_accepts_a_numeric_string_price(): void
    {
        $this->assertSame(315000, Discount::total('350000', '10%'));
    }

    public function test_percent_rounds_half_up(): void
    {
        $this->assertSame(100, Discount::total(105, '5%')); // 5.25 → 5
        $this->assertSame(104, Discount::total(110, '5%')); // 5.5 → 6
    }

    public static function labels(): array
    {
        return [[null, ''], ['', ''], ['0', ''], ['10%', '10%'], ['50000', 'Rp50,000'], ['abc', 'Invalid discount'], ['101%', 'Invalid discount']];
    }

    #[DataProvider('labels')]
    public function test_label(?string $discount, string $expected): void
    {
        $this->assertSame($expected, Discount::label($discount));
    }

    public static function inputs(): array
    {
        return [
            ['0', true], ['50000', true], ['10%', true], ['100%', true], ['350000', true],
            ['101%', false], ['abc', false], ['5.5', false], ['-5', false], ['350001', false], ['10 %', false], ['', false],
            ["400000\n", false], ["50000\n", false],
        ];
    }

    #[DataProvider('inputs')]
    public function test_rules(string $value, bool $passes): void
    {
        $validator = Validator::make(['d' => $value], ['d' => ['required', ...Discount::rules(350000)]], Discount::messages('d'));

        $this->assertSame($passes, $validator->passes(), json_encode($validator->errors()->all()));
    }

    public function test_messages(): void
    {
        $format = Validator::make(['d' => 'abc'], ['d' => ['required', ...Discount::rules(350000)]], Discount::messages('d'));
        $this->assertSame('Use a whole Rupiah amount (e.g. 50000) or a percentage from 0% to 100%.', $format->errors()->first('d'));

        $tooBig = Validator::make(['d' => '400000'], ['d' => ['required', ...Discount::rules(350000)]], Discount::messages('d'));
        $this->assertSame('The discount cannot be more than the price.', $tooBig->errors()->first('d'));
    }

    public function test_amount_check_is_skipped_when_the_price_is_not_numeric(): void
    {
        $this->assertTrue(Validator::make(['d' => '400000'], ['d' => Discount::rules('abc')])->passes());
    }

    public function test_display_tolerates_whitespace_around_a_stored_discount(): void
    {
        $this->assertSame(315000, Discount::total(350000, " 10%\n"));
        $this->assertSame('Rp50,000', Discount::label(' 50000 '));
    }
}
