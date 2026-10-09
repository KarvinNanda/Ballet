<?php

namespace Tests\Feature\Finance;

use App\Models\Stock;
use App\Models\Student;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Staff\StaffTestCase;

/**
 * Finance and buyer pages. asRole('finance') logs in the seeded finance user; asBuyer() makes a buyer (the seed has none).
 * Inherits rowFor() and formFields() from StaffTestCase.
 */
abstract class FinanceTestCase extends StaffTestCase
{
    /** A transaction of a new student (LongName from 'student', students.Status from 'student_status') in the first class. */
    protected function transaction(array $attributes = []): Transaction
    {
        $student = Student::factory()->create([
            'LongName' => $attributes['student'] ?? 'Fin Kid',
            'Status' => $attributes['student_status'] ?? 'aktif',
        ]);
        unset($attributes['student'], $attributes['student_status']);

        $id = DB::table('transactions')->insertGetId(array_merge([
            'students_id' => $student->id,
            'class_transactions_id' => DB::table('class_transactions')->orderBy('id')->value('id'),
            'transaction_date' => '2026-09-01',
            'payment_status' => 'Unpaid',
            'price' => 350000,
            'discount' => '0',
            'transaction_quota' => 4,
            'desc' => '-',
            'created_at' => now(),
            'updated_at' => now(),
        ], $attributes));

        return Transaction::findOrFail($id);
    }

    /** The class transaction() uses by default, with a frozen class price (888,000) and a course price (999,000) that no test transaction has. */
    protected function firstClassWithOtherPrices(): int
    {
        $classId = (int) DB::table('class_transactions')->orderBy('id')->value('id');
        DB::table('class_transactions')->where('id', $classId)->update(['class_transaction_price' => 888000]);
        DB::table('class_types')
            ->where('id', DB::table('class_transactions')->where('id', $classId)->value('class_type_id'))
            ->update(['class_price' => 999000]);

        return $classId;
    }

    protected function stockItem(string $name, int $quantity, string $size = 'M'): Stock
    {
        $id = DB::table('stocks')->insertGetId([
            'name' => $name, 'size' => $size, 'quantity' => $quantity, 'created_at' => now(), 'updated_at' => now(),
        ]);

        return Stock::findOrFail($id);
    }

    protected function newBuyer(string $name = 'Toko Kasir'): User
    {
        return User::factory()->create(['role' => 'buyer', 'name' => $name]);
    }

    /** Log in as $buyer (a new one by default); safe to call repeatedly, like asRole(). */
    protected function asBuyer(?User $buyer = null): static
    {
        $this->flushSession();
        $this->app['auth']->forgetGuards();

        return $this->actingAs($buyer ?? $this->newBuyer());
    }
}
