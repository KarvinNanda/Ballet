<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RateLimitTest extends TestCase
{
    use RefreshDatabase;

    private function post31(User $user): \Illuminate\Testing\TestResponse
    {
        $typeId = DB::table('class_types')->value('id');
        $response = null;
        for ($i = 0; $i < 31; $i++) {
            $response = $this->actingAs($user)->post(route('head.class-type.edit'), ['typeID' => $typeId]); // read-only POST page
        }

        return $response;
    }

    public function test_31st_write_in_a_minute_is_429(): void
    {
        $this->post31(User::where('role', 'head')->firstOrFail())->assertStatus(429);
    }

    public function test_gets_are_not_limited(): void
    {
        $head = User::where('role', 'head')->firstOrFail();
        for ($i = 0; $i < 40; $i++) {
            $this->actingAs($head)->get(route('head.student.index'))->assertOk();
        }
    }

    public function test_limit_is_per_user(): void
    {
        $this->post31(User::where('role', 'head')->firstOrFail());
        $other = User::factory()->create(['role' => 'head']);
        $this->flushSession();
        $this->app['auth']->forgetGuards();
        $this->actingAs($other)->post(route('head.class-type.edit'), ['typeID' => DB::table('class_types')->value('id')])->assertOk();
    }

    public function test_account_creation_is_limited_to_10_per_hour(): void
    {
        \Illuminate\Support\Facades\Mail::fake();
        $head = User::where('role', 'head')->firstOrFail();
        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($head)->post(route('head.teacher.store'), [
                'inputName' => "T{$i}", 'inputEmail' => "t{$i}@example.com", 'inputDate_of_Birth' => '1995-01-01',
                'inputAddress' => 'Jl', 'inputPhone' => '081234567890',
            ])->assertSessionHasNoErrors();
        }
        $this->post(route('head.teacher.store'), [
            'inputName' => 'T10', 'inputEmail' => 't10@example.com', 'inputDate_of_Birth' => '1995-01-01',
            'inputAddress' => 'Jl', 'inputPhone' => '081234567890',
        ])->assertStatus(429);
    }
}
