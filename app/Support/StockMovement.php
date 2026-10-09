<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockMovement
{
    /** Adds ('in') or removes ('out') $qty of one stock item and logs it in report_stocks. Never goes below zero. */
    public static function record(int $stockId, string $type, int $qty): void
    {
        if (! in_array($type, ['in', 'out'], true) || $qty < 1) {
            throw new InvalidArgumentException("Bad stock movement: {$type} {$qty}");
        }

        DB::transaction(function () use ($stockId, $type, $qty) {
            $stock = DB::table('stocks')->where('id', $stockId)->lockForUpdate()->first();
            $current = (int) $stock->quantity;

            if ($type === 'out' && $qty > $current) {
                throw new InsufficientStock("Only {$current} left in stock.");
            }

            // first_qty: the quantity when this item was first reported (kept from the first report row).
            $firstQty = DB::table('report_stocks')->where('stock_id', $stockId)->value('first_qty') ?? $current;

            DB::table('report_stocks')->insert([
                'stock_id' => $stockId,
                $type => $qty, // $type is 'in' or 'out', checked above
                'report_date' => now()->setTimezone('GMT+7')->toDateString(),
                'first_qty' => $firstQty,
            ]);

            DB::table('stocks')->where('id', $stockId)->update([
                'quantity' => $type === 'in' ? $current + $qty : $current - $qty,
            ]);
        });
    }
}
