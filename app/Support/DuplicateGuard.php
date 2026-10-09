<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class DuplicateGuard
{
    /** Stops a migration before it adds a unique index to data that would violate it. Never deletes anything. */
    public static function assertNone(string $table, array $columns): void
    {
        $query = DB::table($table)->select($columns)->groupBy($columns)->havingRaw('count(*) > 1');
        foreach ($columns as $column) {
            $query->whereNotNull($column);
        }
        $groups = DB::query()->fromSub($query, 'd')->count();

        if ($groups > 0) {
            throw new RuntimeException(sprintf(
                '%s has %d duplicated (%s) group(s). Clean them before running this migration; nothing was changed.',
                $table, $groups, implode(', ', $columns),
            ));
        }
    }
}
