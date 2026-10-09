<?php

namespace App\Rules;

/**
 * Shared rules for any date or datetime input. Laravel's plain `date` rule accepts a 5-digit year
 * (browsers allow typing one in <input type="date">), which then fails or is saved as another date in MySQL.
 */
class BoundedDate
{
    public const MIN = '1900-01-01';
    public const BEFORE = '2101-01-01';
    /** PHP's date parser reads "20266-01-11" as 2006-01-11, so the bounds alone do not catch it: require a 4-digit year. */
    public const SHAPE = '/^\\d{4}-\\d{2}-\\d{2}([T ]\\d{2}:\\d{2}(:\\d{2})?)?$/';

    /** @param list<string> $extra e.g. ['before:tomorrow'] */
    public static function rules(bool $required = true, array $extra = []): array
    {
        return array_merge(
            [$required ? 'required' : 'nullable', 'bail', 'date', 'regex:' . self::SHAPE, 'after_or_equal:' . self::MIN, 'before:' . self::BEFORE],
            $extra,
        );
    }
}
