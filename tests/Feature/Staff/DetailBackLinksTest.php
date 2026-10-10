<?php

namespace Tests\Feature\Staff;

use Illuminate\Support\Facades\DB;

/** Every staff detail page offers a Back button to its list, for admin and head. */
class DetailBackLinksTest extends StaffTestCase
{
    private function assertBackTo(string $html, string $url, string $label): void
    {
        $this->assertStringContainsString(
            '<a href="'.e($url).'" class="btn btn-outline-secondary"><i class="bi bi-arrow-left" aria-hidden="true"></i> '.$label.'</a>',
            $html
        );
    }

    public function test_detail_pages_link_back_to_their_lists(): void
    {
        $student = (int) DB::table('students')->value('id');
        $transaction = (int) DB::table('transactions')->whereNotNull('students_id')->value('id');
        $active = (int) DB::table('class_transactions')->where('is_freeze', 0)->value('id');
        $frozen = (int) DB::table('class_transactions')->where('is_freeze', 1)->value('id');
        if ($frozen === 0) {
            DB::table('class_transactions')->where('id', '!=', $active)->limit(1)->update(['is_freeze' => 1]);
            $frozen = (int) DB::table('class_transactions')->where('is_freeze', 1)->value('id');
        }

        foreach (['admin', 'head'] as $role) {
            $this->asRole($role);
            $this->assertBackTo($this->get(route("{$role}.student.show", $student))->assertOk()->getContent(), route("{$role}.student.index"), 'Back to students');
            $this->assertBackTo($this->get(route("{$role}.transaction.show", $transaction))->assertOk()->getContent(), route("{$role}.transaction.index"), 'Back to transactions');
            $this->assertBackTo($this->get(route("{$role}.class.show", $active))->assertOk()->getContent(), route("{$role}.class.index"), 'Back to classes');
            $this->assertBackTo($this->get(route("{$role}.class.freeze.show", $frozen))->assertOk()->getContent(), route("{$role}.class.freeze.index"), 'Back to frozen classes');
        }
    }
}
