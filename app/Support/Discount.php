<?php

namespace App\Support;

use Closure;

/**
 * Transaction discount, stored as text: '' or '0' (none), a whole Rupiah amount ('50000') or a percentage ('10%').
 * Server-side source of truth; public/assets/js/pages/transaction-form.js mirrors total() for the live preview.
 */
final class Discount
{
    private const PATTERN = '/^(\d{1,10}|(100|\d{1,2})%)$/D';

    /** Format rules without a presence rule; the caller prepends 'required' or 'nullable'. */
    public static function rules(mixed $price): array
    {
        return [
            'string',
            'max:20',
            'regex:'.self::PATTERN,
            function (string $attribute, mixed $value, Closure $fail) use ($price) {
                if (is_string($value) && ctype_digit($value) && is_numeric($price) && (int) $value > (int) $price) {
                    $fail('The discount cannot be more than the price.');
                }
            },
        ];
    }

    public static function messages(string $attribute): array
    {
        return [$attribute.'.regex' => 'Use a whole Rupiah amount (e.g. 50000) or a percentage from 0% to 100%.'];
    }

    /** Price after the discount, or null when the stored discount is not valid for this price. */
    public static function total(int|string|null $price, ?string $discount): ?int
    {
        $price = (int) $price;
        $discount = trim((string) $discount);

        if ($discount === '' || $discount === '0') {
            return $price;
        }
        if (! preg_match(self::PATTERN, $discount)) {
            return null;
        }
        if (str_ends_with($discount, '%')) {
            return $price - (int) round($price * (int) rtrim($discount, '%') / 100);
        }

        return (int) $discount <= $price ? $price - (int) $discount : null;
    }

    public static function label(?string $discount): string
    {
        $discount = trim((string) $discount);

        if ($discount === '' || $discount === '0') {
            return '';
        }
        if (! preg_match(self::PATTERN, $discount)) {
            return 'Invalid discount';
        }

        return str_ends_with($discount, '%') ? $discount : 'Rp'.number_format((int) $discount);
    }
}
