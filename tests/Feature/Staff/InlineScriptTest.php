<?php

namespace Tests\Feature\Staff;

use App\Models\User;
use App\Support\AttendanceWindow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * Pages listed here must run under the enforced `script-src 'self'` CSP (App\Http\Middleware\SecurityHeaders):
 * no inline <script> body and no on*="" handler attributes. SP2b/SP2c add their pages to pages().
 */
class InlineScriptTest extends StaffTestCase
{
    /** A javascript: URL in a link, image or form attribute runs code just like an inline script. */
    private const JAVASCRIPT_URL = '/\b(?:href|src|action|formaction)\s*=\s*["\']?\s*javascript:/i';

    /** @return array<string, \Closure(): string> page label => url builder (runs after the app boots) */
    private function pages(string $role): array
    {
        return [
            'home' => fn () => route($role),
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

    public function test_teacher_pages_have_no_inline_scripts_or_handlers(): void
    {
        $teacher = User::where('email', 'teacher@gmail.com')->firstOrFail();
        $recordedIds = DB::table('header_absens')->pluck('schedules_id');
        $own = fn () => DB::table('schedules as s')->join('mapping_class_teachers as m', 'm.class_id', 's.class_id')
            ->where('m.user_id', $teacher->id)->select('s.id', 's.class_id', 's.date');
        // The seed starts today's sessions an hour before it runs, so one is inside the attendance window.
        $open = $own()->whereNotIn('s.id', $recordedIds)->get()->first(fn ($s) => AttendanceWindow::isOpen($s->date));
        $recorded = $own()->whereIn('s.id', $recordedIds)->value('s.id');
        $this->assertNotNull($open, 'seed has no open session for teacher@gmail.com');
        $this->assertNotNull($recorded, 'seed has no recorded session for teacher@gmail.com');
        $classId = (int) $open->class_id;
        // The update form opens only for a session that has not started, so give the class one next month.
        $upcomingId = DB::table('schedules')->insertGetId(['class_id' => $classId, 'date' => now()->addMonth()->toDateTimeString(), 'created_at' => now(), 'updated_at' => now()]);

        $this->actingAs($teacher);

        $getPages = [
            'home' => route('teacher'),
            'home searched' => route('teacher', ['keyword' => 'a']),
            'my classes' => route('viewClass'),
            'schedules' => route('viewAllScheduleTeacher', $teacher->id),
            'class schedule' => route('viewScheduleClassTeacher', $classId),
            'add schedule' => route('viewaddScheduleClass', $classId),
            'weekly schedules' => route('viewaddMultipleScheduleClass', $classId),
            'update schedule' => route('viewUpdateScheduleClassTeacher', ['scheduleId' => $upcomingId]),
        ];
        foreach ($getPages as $label => $url) {
            $this->assertNoInlineCode($this->get($url)->assertOk()->getContent(), "teacher {$label}");
        }

        $postPages = [
            'students' => route('viewDetailTeacher', $classId),
            'attendance form' => route('viewAbsen', $open->id),
            'recorded attendance' => route('viewAbsen', $recorded),
        ];
        foreach ($postPages as $label => $url) {
            $this->assertNoInlineCode($this->post($url)->assertOk()->getContent(), "teacher {$label}");
        }
    }

    public function test_finance_buyer_and_profile_pages_have_no_inline_scripts_or_handlers(): void
    {
        $stockId = (int) DB::table('stocks')->where('quantity', '>', 0)->value('id');
        $this->assertNotSame(0, $stockId, 'seed has no stock item in stock');

        $financePages = [
            'stock list' => route('finance'),
            'stock list searched' => route('finance', ['search' => 'a']),
            'stock sorted' => route('financeStockViewSorting', ['value' => 'name', 'sort' => 'asc']),
            'record stock in' => route('in', $stockId),
            'transactions' => route('financeTransaction'),
            'transactions all' => route('financeTransaction', ['status' => 'all']),
            'transactions searched' => route('searchTransaction', ['search' => 'a']),
            'transactions sorted' => route('financeTransactionSorting', ['column' => 'price']),
            'record payment' => route('paidTransaction', $this->unpaidTransactionId()),
            'stock report' => route('financeStockReport'),
            'teacher report' => route('financeTeacherReportPage'),
            'student report' => route('financeStudentReportPage'),
        ];
        foreach ($financePages as $label => $url) {
            $this->assertNoInlineCode($this->asRole('finance')->get($url)->assertOk()->getContent(), "finance {$label}");
        }

        $buyer = User::factory()->create(['role' => 'buyer']);
        $buyerPages = [
            'item list' => route('buyer'),
            'item list sorted' => route('buyerSorting', ['value' => 'name', 'type' => 'asc', 'search' => 'a']),
            'sell' => route('buyingItem', $stockId),
            'profile' => route('change-profile-page'),
            'password' => route('change-password-page'),
        ];
        $this->flushSession();
        $this->app['auth']->forgetGuards();
        $this->actingAs($buyer);
        foreach ($buyerPages as $label => $url) {
            $this->assertNoInlineCode($this->get($url)->assertOk()->getContent(), "buyer {$label}");
        }

        foreach (['admin', 'head', 'teacher', 'finance'] as $role) {
            foreach (['profile' => route('change-profile-page'), 'password' => route('change-password-page')] as $label => $url) {
                $this->assertNoInlineCode($this->asRole($role)->get($url)->assertOk()->getContent(), "{$role} {$label}");
            }
        }
    }

    public function test_no_page_view_source_has_inline_scripts_or_handlers(): void
    {
        $offenders = [];
        foreach (File::allFiles(resource_path('views')) as $file) {
            // PDF templates are rendered by dompdf into a PDF, never served as an HTML page under the CSP.
            if (! str_ends_with($file->getFilename(), '.blade.php') || $file->getFilename() === 'print.blade.php') {
                continue;
            }
            $source = $file->getContents();
            if (self::bladeSourceHasInlineCode($source)) {
                $offenders[] = $file->getRelativePathname();
            }
        }

        $this->assertSame([], $offenders, 'inline <script> or on* handler in: '.implode(', ', $offenders));
    }

    public function test_source_scan_helper_flags_handlers_hidden_behind_blade_expressions(): void
    {
        $flagged = [
            '<a href="{{ route(\'x\', $a->id) }}" onclick="f()">x</a>',
            '<option @selected($a->b) onchange="g()">',
            '<div data-x="{{ json_encode([\'a\' => 1]) }}" onload="h()">',
            '<script>alert(1)</script>',
            '<a href="javascript:void(0)">x</a>',
            '<a href=" JavaScript:alert(1)">x</a>',
        ];
        foreach ($flagged as $source) {
            $this->assertTrue(self::bladeSourceHasInlineCode($source), "should be flagged: {$source}");
        }

        $clean = [
            '<script src="{{ asset(\'assets/js/app.js\') }}"></script>',
            '<a href="{{ $u->url }}" class="btn">Open</a>',
            '{{-- <button onclick="x()"> --}}',
            '<a href="{{ $url }}">x</a>',
            '<p>javascript: is not allowed</p>',
        ];
        foreach ($clean as $source) {
            $this->assertFalse(self::bladeSourceHasInlineCode($source), "should not be flagged: {$source}");
        }
    }

    public function test_rendered_guard_flags_javascript_urls(): void
    {
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->assertNoInlineCode('<a class="btn" href="javascript:alert(1)">x</a>', 'self-test');
    }

    public function test_error_and_empty_branches_have_no_inline_code(): void
    {
        $nothing = 'zzz-no-such-thing';
        $pages = [
            'student list empty' => ['head', route('head.student.index', ['keyword' => $nothing])],
            'stock list empty' => ['head', route('head.stock.index', ['search' => $nothing])],
            'finance transactions empty' => ['finance', route('financeTransaction', ['search' => $nothing])],
        ];
        foreach ($pages as $label => [$role, $url]) {
            $html = $this->asRole($role)->get($url)->assertOk()->getContent();
            $this->assertStringContainsString('empty-state', $html, "{$label}: not the empty branch");
            $this->assertNoInlineCode($html, $label);
        }

        // Error branches: a failed POST, then the page it redirects back to.
        $this->asRole('head')->from(route('head.student.create'))->post(route('head.student.store'), [])->assertRedirect(); // a session() assertion would wipe the flashed errors
        $html = $this->get(route('head.student.create'))->assertOk()->getContent();
        $this->assertStringContainsString('is-invalid', $html, 'student create: not the error branch');
        $this->assertNoInlineCode($html, 'student create with errors');

        $this->asRole('finance')->from(route('change-password-page'))->post(route('change-password'), [])->assertRedirect();
        $html = $this->get(route('change-password-page'))->assertOk()->getContent();
        $this->assertStringContainsString('is-invalid', $html, 'password: not the error branch');
        $this->assertNoInlineCode($html, 'password with errors');

        $stockId = (int) DB::table('stocks')->where('quantity', '>', 0)->value('id');
        $quantity = (int) DB::table('stocks')->where('id', $stockId)->value('quantity');
        $buyer = User::factory()->create(['role' => 'buyer']);
        $this->flushSession();
        $this->app['auth']->forgetGuards(); // leave the finance login of the request above
        $this->actingAs($buyer)->from(route('buyingItem', $stockId))
            ->post(route('buying', $stockId), ['name' => 'Over Stock', 'qty' => $quantity + 1])->assertRedirect();
        $html = $this->get(route('buyingItem', $stockId))->assertOk()->getContent();
        $this->assertStringContainsString('is-invalid', $html, 'sell: not the error branch');
        $this->assertNoInlineCode($html, 'sell with an over-stock error');
    }

    public function test_upper_case_sort_direction_shows_descending(): void
    {
        $html = $this->asRole('head')->get(route('head.student.sort', ['name', 'DESC']))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<th scope="col"\s+aria-sort="descending"[^>]*>\s*<a href="[^"]*" class="sort-link">Name <i class="bi bi-arrow-down"/', $html);
    }

    /**
     * Blade comments and echoes are removed first, and `->` / `=>` are neutralised: a `>` inside PHP would
     * otherwise end the `[^>]+` of the handler regex early and hide an on* attribute after it.
     */
    private static function bladeSourceHasInlineCode(string $source): bool
    {
        $source = preg_replace(['/\{\{--.*?--\}\}/s', '/\{!!.*?!!\}/s', '/\{\{.*?\}\}/s'], '', $source);
        $source = str_replace(['->', '=>'], '__', $source);

        return preg_match('/<script\b(?![^>]*\ssrc=)[^>]*>/i', $source) === 1
            || preg_match('/<[^>]+\son[a-z]+\s*=/i', $source) === 1
            || preg_match(self::JAVASCRIPT_URL, $source) === 1;
    }

    private function assertNoInlineCode(string $html, string $label): void
    {
        $this->assertDoesNotMatchRegularExpression('/<script\b(?![^>]*\ssrc=)[^>]*>/i', $html, "{$label}: inline <script>");
        $this->assertDoesNotMatchRegularExpression('/<[^>]+\son[a-z]+\s*=/i', $html, "{$label}: on* handler attribute");
        $this->assertDoesNotMatchRegularExpression(self::JAVASCRIPT_URL, $html, "{$label}: javascript: URL");
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
