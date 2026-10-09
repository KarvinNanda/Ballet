<?php

namespace Tests\Feature\Gaps;

use Illuminate\Support\Facades\DB;
use Tests\Feature\Finance\FinanceTestCase;

/** A name with HTML, an apostrophe and double quotes must reach the page escaped, also inside data-confirm="" attributes. */
class EscapingTest extends FinanceTestCase
{
    private const NAME = '<b>O\'Neil "Q"</b>';

    private function assertEscapedNotRaw(string $html): void
    {
        $this->assertStringContainsString(e(self::NAME), $html);
        $this->assertStringNotContainsString('<b>O\'Neil', $html);
        $this->assertStringNotContainsString('<b>O&#039;Neil', $html);
    }

    public function test_student_list_and_detail_escape_the_name(): void
    {
        $transaction = $this->transaction(['student' => self::NAME]);
        $student = $transaction->Students;
        DB::table('students')->where('id', $student->id)->update(['ShortName' => self::NAME]);
        $this->asRole('head');

        $list = $this->get(route('head.student.index', ['keyword' => 'Neil']))->assertOk()->getContent();
        $this->assertEscapedNotRaw($list);
        $this->assertStringContainsString('data-confirm="Set '.e(self::NAME).' to ', $list);

        $detail = $this->get(route('head.student.show', $student))->assertOk()->getContent();
        $this->assertEscapedNotRaw($detail);
        $this->assertStringContainsString('value="'.e(self::NAME).'"', $detail); // the ShortName input
    }

    public function test_transaction_list_and_detail_escape_the_name_in_text_and_confirm_messages(): void
    {
        $transaction = $this->transaction(['student' => self::NAME]);
        $this->asRole('head');
        $confirm = 'data-confirm="Delete this transaction of '.e(self::NAME).'? This cannot be undone."';

        $list = $this->get(route('head.transaction.index', ['search' => 'Neil']))->assertOk()->getContent();
        $this->assertEscapedNotRaw($list);
        $this->assertStringContainsString($confirm, $list);

        $detail = $this->get(route('head.transaction.show', $transaction))->assertOk()->getContent();
        $this->assertEscapedNotRaw($detail);
        $this->assertStringContainsString($confirm, $detail);
    }
}
