<?php

namespace Tests\Feature\Staff;

/** /head/admin manages admin accounts only; other users behind the same {user} binding are 404. */
class HeadAdminAccountTest extends StaffTestCase
{
    private function payload(): array
    {
        return [
            'inputName' => 'Hijacked', 'inputEmail' => 'hijacked@example.com', 'inputDate_of_Birth' => '1990-01-01',
            'inputAddress' => 'x', 'inputBonus' => 0, 'inputPhone' => '0812345678',
        ];
    }

    public function test_head_cannot_edit_or_delete_a_non_admin_user(): void
    {
        foreach (['teacher', 'finance', 'head'] as $role) {
            $target = $this->user($role);
            $before = $target->fresh()->only(['name', 'email']);

            $this->asRole('head')->get(route('headAdminUpdatePage', $target))->assertNotFound();
            $this->post(route('headAdminUpdate', $target), $this->payload())->assertNotFound();
            $this->post(route('AdminDelete', $target))->assertNotFound();

            $this->assertNotNull($target->fresh(), "{$role} deleted");
            $this->assertSame($before, $target->fresh()->only(['name', 'email']), "{$role} changed");
        }
    }

    public function test_head_can_still_edit_and_delete_an_admin(): void
    {
        $admin = $this->user('admin');
        $this->asRole('head')->get(route('headAdminUpdatePage', $admin))->assertOk();
        $this->post(route('headAdminUpdate', $admin), $this->payload())->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('Hijacked', $admin->fresh()->name);
        $this->post(route('AdminDelete', $admin))->assertRedirect();
        $this->assertNull($admin->fresh());
    }
}
