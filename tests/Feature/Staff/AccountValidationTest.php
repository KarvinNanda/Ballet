<?php

namespace Tests\Feature\Staff;

use App\Models\User;
use Illuminate\Support\Facades\Mail;

class AccountValidationTest extends StaffTestCase
{
    private function payload(array $override = []): array
    {
        return array_merge([
            'inputName' => 'Baru', 'inputEmail' => 'baru@example.com', 'inputDate_of_Birth' => '1995-01-01',
            'inputAddress' => 'Jl. A', 'inputPhone' => '081234567890', 'inputBonus' => 30,
        ], $override);
    }

    public static function stores(): array
    {
        return [
            'teacher store' => ['head.teacher.store'],
            'finance store' => ['head.finance.store'],
            'admin insert' => ['AdminAdd'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('stores')]
    public function test_store_with_an_existing_email_is_a_validation_error_and_sends_no_mail(string $route): void
    {
        Mail::fake();
        $before = User::count();
        $this->asRole('head')->post(route($route), $this->payload(['inputEmail' => 'teacher@gmail.com']))
            ->assertSessionHasErrors('inputEmail');
        $this->assertSame($before, User::count());
        Mail::assertNothingSent();
    }

    public function test_update_with_another_users_email_is_a_validation_error(): void
    {
        $teacher = User::where('email', 'sari.teacher@gmail.com')->firstOrFail();
        $finance = User::where('role', 'finance')->firstOrFail();
        $admin = User::where('role', 'admin')->firstOrFail();

        $this->asRole('head')->post(route('head.teacher.update', $teacher), $this->payload(['inputEmail' => 'head@gmail.com']))->assertSessionHasErrors('inputEmail');
        $this->post(route('head.finance.update', $finance), $this->payload(['inputEmail' => 'head@gmail.com']))->assertSessionHasErrors('inputEmail');
        $this->post(route('headAdminUpdate', $admin), $this->payload(['inputEmail' => 'head@gmail.com']))->assertSessionHasErrors('inputEmail');

        $this->assertSame('sari.teacher@gmail.com', $teacher->fresh()->email);
        $this->assertSame($finance->email, $finance->fresh()->email);
        $this->assertSame($admin->email, $admin->fresh()->email);
    }

    public function test_role_check_comes_before_validation_on_update(): void
    {
        $finance = User::where('role', 'finance')->firstOrFail();
        $admin = User::where('role', 'admin')->firstOrFail();
        $teacher = User::where('role', 'teacher')->firstOrFail();
        $bad = $this->payload(['inputEmail' => 'not-an-email']);

        $this->asRole('admin')->post(route('admin.finance.update', $finance), $bad)->assertForbidden();
        $this->assertSame($finance->email, $finance->fresh()->email);

        $this->asRole('head');
        $this->post(route('head.teacher.update', $admin), $bad)->assertNotFound();
        $this->post(route('head.finance.update', $teacher), $bad)->assertNotFound();
        $this->post(route('headAdminUpdate', $teacher), $bad)->assertNotFound();
        $this->assertSame($admin->email, $admin->fresh()->email);
        $this->assertSame($teacher->email, $teacher->fresh()->email);
    }

    public function test_teacher_bonus_must_be_between_0_and_100(): void
    {
        $teacher = User::where('email', 'sari.teacher@gmail.com')->firstOrFail();
        $teacher->forceFill(['percent' => 30])->save();
        $this->asRole('head');

        foreach ([101, -1] as $bonus) {
            $this->post(route('head.teacher.update', $teacher), $this->payload(['inputEmail' => $teacher->email, 'inputBonus' => $bonus]))
                ->assertSessionHasErrors('inputBonus');
            $this->assertEquals(30, $teacher->fresh()->percent);
        }
        foreach ([100, 0] as $bonus) {
            $this->post(route('head.teacher.update', $teacher), $this->payload(['inputEmail' => $teacher->email, 'inputBonus' => $bonus]))
                ->assertSessionHasNoErrors();
            $this->assertEquals($bonus, $teacher->fresh()->percent);
        }
    }

    public function test_update_keeping_the_own_email_is_fine(): void
    {
        $teacher = User::where('email', 'sari.teacher@gmail.com')->firstOrFail();
        $this->asRole('head')->post(route('head.teacher.update', $teacher), $this->payload(['inputEmail' => $teacher->email]))
            ->assertSessionHasNoErrors();
    }

    public function test_email_longer_than_the_column_is_a_validation_error(): void
    {
        Mail::fake();
        $this->asRole('head')->post(route('head.teacher.store'), $this->payload(['inputEmail' => str_repeat('a', 250).'@example.com']))
            ->assertSessionHasErrors('inputEmail');
    }

    public function test_admin_still_cannot_update_finance(): void
    {
        $finance = User::where('role', 'finance')->firstOrFail();
        $this->asRole('admin')->post(route('admin.finance.update', $finance), $this->payload(['inputEmail' => 'x@example.com']))->assertForbidden();
    }

    public function test_array_search_on_admin_list_is_not_a_500(): void
    {
        $this->asRole('head')->post(route('searchAdmin'), ['search' => ['x']])->assertStatus(302);
    }
}
