<?php

namespace Tests\Feature\Staff;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Pages listed here must run under `script-src 'self'` (SP2e enforces it):
 * no inline <script> body and no on*="" handler attributes. SP2b/SP2c add their pages to pages().
 */
class InlineScriptTest extends StaffTestCase
{
    /** @return array<string, \Closure(): string> page label => url builder (runs after the app boots) */
    private function pages(string $role): array
    {
        return [
            'student list' => fn () => route("{$role}.student.index"),
            'student create' => fn () => route("{$role}.student.create"),
            'student detail' => fn () => route("{$role}.student.show", $this->studentAndFreeClass()[0]),
            'student add class' => fn () => route("{$role}.student.class.create", $this->studentAndFreeClass()[0]),
            'transaction list' => fn () => route("{$role}.transaction.index"),
            'transaction detail' => fn () => route("{$role}.transaction.show", $this->unpaidTransactionId()),
            'transaction add' => fn () => route("{$role}.transaction.create"),
            'transaction update' => fn () => route("{$role}.transaction.edit", $this->unpaidTransactionId()),
            'class list' => fn () => route("{$role}.class.index"),
            'class detail' => fn () => route("{$role}.class.show", $this->scheduledClassId(0)),
            'class add' => fn () => route("{$role}.class.create"),
            'class add teacher' => fn () => route("{$role}.class.teacher.create", $this->scheduledClassId(0)),
            'class add student' => fn () => route("{$role}.class.student.create", $this->scheduledClassId(0)),
            'frozen list' => fn () => route("{$role}.class.freeze.index"),
            'frozen detail' => fn () => route("{$role}.class.freeze.show", $this->scheduledClassId(1)),
            'teacher list' => fn () => route("{$role}.teacher.index"),
            'teacher add' => fn () => route("{$role}.teacher.create"),
            'teacher update' => fn () => route("{$role}.teacher.edit", $this->teacherId()),
            'teacher replace' => fn () => route("{$role}.teacher.switch", $this->teacherId()),
            'finance list' => fn () => route("{$role}.finance.index"),
            'finance add' => fn () => route("{$role}.finance.create"),
            'stock list' => fn () => route("{$role}.stock.index"),
            'stock sorted' => fn () => route("{$role}.stock.sort", ['column' => 'name', 'direction' => 'asc']),
            'schedule list' => fn () => route("{$role}.schedule.index", $this->scheduledClassId(0)),
            'schedule add' => fn () => route("{$role}.schedule.create", $this->scheduledClassId(0)),
            'schedule update' => fn () => route("{$role}.schedule.edit", DB::table('schedules')->value('id')),
            'weekly schedules for a class' => fn () => route("{$role}.schedule.multiple.create", $this->scheduledClassId(0)),
            'weekly schedules' => fn () => route("{$role}.schedule.multiple.create"),
            'course list' => fn () => route("{$role}.class-type.index"),
            'course add' => fn () => route("{$role}.class.course.create"),
            'report active students' => fn () => route("{$role}.report.active-student"),
            'report stock' => fn () => route("{$role}.report.stock"),
            'report class attendance' => fn () => route("{$role}.report.class"),
            'report teacher' => fn () => route("{$role}.report.teacher"),
        ];
    }

    /** @return array<string, \Closure(): string> pages only head may open (Gates or head-only routes) */
    private function headOnlyPages(): array
    {
        return [
            'finance update' => fn () => route('head.finance.edit', User::where('role', 'finance')->value('id')),
            'stock add' => fn () => route('head.stock.create'),
            'stock update' => fn () => route('head.stock.edit', DB::table('stocks')->value('id')),
            'attendance' => fn () => route('head.attendance.edit', DB::table('schedules')->value('id')),
            'admin list' => fn () => route('headAdminPage'),
            'admin list searched' => fn () => route('headAdminPage', ['search' => 'a']),
            'admin add' => fn () => route('headAdminAddPage'),
            'admin update' => fn () => route('headAdminUpdatePage', User::where('role', 'admin')->value('id')),
            'rule list' => fn () => route('Rules'),
            'rule add' => fn () => route('RulesAddPage'),
            'rule update' => fn () => route('RulesUpdatePage', DB::table('rules')->value('id')),
        ];
    }

    public function test_pages_have_no_inline_scripts_or_handlers(): void
    {
        foreach (['admin', 'head'] as $role) {
            foreach ($this->pages($role) as $label => $url) {
                $this->assertNoInlineCode($this->asRole($role)->get($url())->assertOk()->getContent(), "{$role} {$label}");
            }
        }
    }

    public function test_head_only_pages_have_no_inline_scripts_or_handlers(): void
    {
        foreach ($this->headOnlyPages() as $label => $url) {
            $this->assertNoInlineCode($this->asRole('head')->get($url())->assertOk()->getContent(), "head {$label}");
        }
    }

    public function test_pages_reached_by_post_have_no_inline_scripts_or_handlers(): void
    {
        foreach (['admin', 'head'] as $role) {
            $html = $this->asRole($role)->post(route("{$role}.class-type.edit"), ['typeID' => DB::table('class_types')->value('id')])->assertOk()->getContent();
            $this->assertNoInlineCode($html, "{$role} course update");
        }

        $html = $this->asRole('head')->post(route('searchAdmin'), ['search' => 'a'])->assertOk()->getContent();
        $this->assertNoInlineCode($html, 'head admin search');
    }

    private function assertNoInlineCode(string $html, string $label): void
    {
        $this->assertDoesNotMatchRegularExpression('/<script\b(?![^>]*\ssrc=)[^>]*>/i', $html, "{$label}: inline <script>");
        $this->assertDoesNotMatchRegularExpression('/<[^>]+\son[a-z]+\s*=/i', $html, "{$label}: on* handler attribute");
    }

    private function teacherId(): int
    {
        return (int) User::where('email', 'teacher@gmail.com')->value('id');
    }

    private function unpaidTransactionId(): int
    {
        return (int) DB::table('transactions')->join('students', 'students.id', 'transactions.students_id')
            ->where('transactions.payment_status', 'Unpaid')->where('students.Status', 'aktif')->value('transactions.id');
    }

    /** A class with a schedule; $frozen 1 makes sure one is frozen. */
    private function scheduledClassId(int $frozen): int
    {
        $db = DB::table('class_transactions')
            ->whereIn('id', DB::table('schedules')->pluck('class_id'));
        $id = (clone $db)->where('is_freeze', $frozen)->where('Status', 'aktif')->value('id');
        if ($id === null && $frozen === 1) {
            $id = (clone $db)->orderByDesc('id')->value('id');
            DB::table('class_transactions')->where('id', $id)->update(['is_freeze' => 1]);
        }

        return (int) $id;
    }
}
