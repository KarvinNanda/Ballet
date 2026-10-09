<?php

namespace Tests\Feature;

use App\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BuyerTest extends TestCase
{
    use RefreshDatabase;

    private function buyer(): User
    {
        return User::factory()->create(['role' => 'buyer']);
    }

    public function test_only_buyers_reach_the_buyer_pages(): void
    {
        foreach (['teacher', 'finance', 'admin', 'head'] as $role) {
            $this->flushSession(); // AuthenticateSession logs out a session whose password hash changed between users
            $this->actingAs(User::where('role', $role)->firstOrFail())->get('/buyer')->assertForbidden();
        }
        $this->flushSession();
        $this->actingAs($this->buyer())->get('/buyer')->assertOk();
    }

    public function test_buying_decrements_stock_and_logs_the_buyer(): void
    {
        $stock = Stock::firstOrFail();
        $stock->forceFill(['quantity' => 5])->save();
        $reports = DB::table('report_stocks')->count();
        $this->actingAs($this->buyer())->post(route('buying', $stock), ['name' => 'Ani', 'qty' => 2])->assertRedirect();
        $this->assertSame(3, (int) $stock->fresh()->quantity);
        $this->assertSame(1, DB::table('buyers')->where('name', 'Ani')->count());
        $this->assertSame($reports + 1, DB::table('report_stocks')->count());
        $row = DB::table('report_stocks')->orderByDesc('id')->first();
        $this->assertSame($stock->id, (int) $row->stock_id);
        $this->assertSame(2, (int) $row->out);
    }

    public function test_buying_more_than_stock_writes_nothing(): void
    {
        $stock = Stock::firstOrFail();
        $stock->forceFill(['quantity' => 1])->save();
        $reports = DB::table('report_stocks')->count();
        $this->actingAs($this->buyer())->post(route('buying', $stock), ['name' => 'Ani', 'qty' => 2])->assertSessionHas('error');
        $this->assertSame(1, (int) $stock->fresh()->quantity);
        $this->assertSame(0, DB::table('buyers')->where('name', 'Ani')->count());
        $this->assertSame($reports, DB::table('report_stocks')->count());
    }

    public function test_unknown_stock_is_404(): void
    {
        $buyer = $this->buyer();
        $this->actingAs($buyer)->get(route('buyingItem', 999999))->assertNotFound();
        $this->post(route('buying', 999999), ['name' => 'Ani', 'qty' => 1])->assertNotFound();
    }
}
