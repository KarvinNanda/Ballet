<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Closure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Sends a valid request with one field replaced by junk (an array, 300 characters, "abc") and asserts no 500.
 * One entry per route that reads input; see test_every_input_route_has_a_case for the coverage guard.
 */
class JunkInputTest extends TestCase
{
    use RefreshDatabase;

    /** @return list<mixed> an array, a string longer than any VARCHAR(255), a non-number, a 5-digit-year date */
    private static function junk(): array
    {
        return [['x'], str_repeat('x', 300), 'abc', '20266-01-11'];
    }

    /** Routes that read no request input (route-bound models only), or are covered elsewhere. Reason per line. */
    private const NO_INPUT = [
        'logout',                                                           // reads no input, only the session
        'head.stock.destroy', 'head.teacher.destroy', 'head.teacher.replace', 'head.finance.destroy',
        'head.transaction.destroy', 'head.student.destroy', 'head.student.class.store',
        'head.class.status', 'head.class.destroy', 'head.class.reset-quota', 'head.class.reset',
        'head.class.teacher.destroy', 'head.class.student.destroy', 'head.class.student.generate-transaction',
        'head.schedule.destroy', 'head.report.class.print', 'head.report.teacher.print',
        'AdminDelete', 'RulesDelete',                                       // route-bound models only
        'viewDetailTeacher', 'viewAbsen', 'deleteScheduleTeacher',          // teacher: route ids only
        'out', 'financeTeacherReport',                                      // finance: route ids only
    ];

    public static function cases(): array
    {
        $none = fn () => [];
        $noPayload = fn () => [];

        return [
            // GET list filters (Task 10)
            'student list' => ['head', 'get', 'head.student.index', $none, $noPayload, ['keyword', 'status']],
            'class list' => ['head', 'get', 'head.class.index', $none, $noPayload, ['keyword', 'status']],
            'freeze list' => ['head', 'get', 'head.class.freeze.index', $none, $noPayload, ['keyword', 'status']],
            'class add student list' => ['head', 'get', 'head.class.student.create', fn () => [DB::table('class_transactions')->value('id')], $noPayload, ['keyword']],
            'teacher list' => ['head', 'get', 'head.teacher.index', $none, $noPayload, ['search']],
            'teacher switch list' => ['head', 'get', 'head.teacher.switch', fn () => [User::where('role', 'teacher')->value('id')], $noPayload, ['search']],
            'finance account list' => ['head', 'get', 'head.finance.index', $none, $noPayload, ['search']],
            'stock list' => ['head', 'get', 'head.stock.index', $none, $noPayload, ['search']],
            'transaction list' => ['head', 'get', 'head.transaction.index', $none, $noPayload, ['search']],
            'schedule multiple page' => ['head', 'get', 'head.schedule.multiple.create', $none, $noPayload, ['classId']],
            'teacher home' => ['teacher', 'get', 'teacher', $none, $noPayload, ['keyword']],
            'finance stock list' => ['finance', 'get', 'finance', $none, $noPayload, ['search']],
            'finance transaction search' => ['finance', 'get', 'searchTransaction', $none, $noPayload, ['search']],
            'buyer list' => ['buyer', 'get', 'buyer', $none, $noPayload, ['search']],
            'admin search' => ['head', 'post', 'searchAdmin', $none, fn () => ['search' => 'a'], ['search']],
            // Later tasks append their entries below this line.
            'student store' => ['head', 'post', 'head.student.store', fn () => [], fn () => [
                'inputLongName' => 'Ani', 'inputNickName' => 'Ani', 'inputParentName' => 'Ibu', 'inputCity' => 'Jkt',
                'inputEmail' => 'junk@example.com', 'inputDate_of_Birth' => '2015-02-02', 'inputAddress' => 'Jl',
                'inputPhone1' => '081234567890', 'inputWhatsapp' => '081234567890', 'inputPostalCode' => '12345',
                'terms_accepted' => '1',
            ], ['inputNis', 'inputLongName', 'inputNickName', 'inputPhone2', 'inputInstagram', 'inputLine', 'inputRekening', 'inputBankName', 'inputNamaPengirim', 'inputEmail', 'inputDate_of_Birth', 'inputPostalCode']],
            'student update' => ['head', 'post', 'head.student.update', fn () => [DB::table('students')->value('id')], fn () => [
                'LongName' => 'Ani', 'nama_orang_tua' => 'Ibu', 'city' => 'Jkt', 'Email' => 'junk@example.com', 'dob' => '2015-02-02',
                'Address' => 'Jl', 'Phone1' => '081234567890', 'Whatsapp' => '081234567890', 'kode_pos' => '12345', 'Quota' => 0, 'Quota_original' => 0,
                'is_new' => 'no', 'status' => 'aktif', 'accountno' => 'JUNK-1', 'sender' => 'Ibu',
            ], ['nis', 'ShortName', 'Phone2', 'Instagram', 'Line', 'bank', 'accountno', 'sender', 'Quota', 'Quota_original', 'MaxQuota', 'EnrollDate', 'status']],
            'student status' => ['head', 'post', 'head.student.status', fn () => [DB::table('students')->value('id')], fn () => ['stats' => 'Active'], ['stats']],
            'transaction store' => ['head', 'post', 'head.transaction.store', fn () => [], fn () => [
                'nis' => DB::table('students')->value('id'), 'class' => DB::table('class_transactions')->value('id'),
                'dateTime' => '2026-01-10', 'Price' => 100,
            ], ['nis', 'class', 'dateTime', 'Price']],
            'transaction update' => ['head', 'post', 'head.transaction.update', fn () => [DB::table('transactions')->where('payment_status', 'Unpaid')->value('id')], fn () => [
                'inputDisc' => '0', 'inputStatus' => 'Unpaid', 'inputJatuhTempo' => '2026-01-10', 'inputQuota' => 3, 'inputPrice' => 100,
            ], ['inputDisc', 'inputStatus', 'inputJatuhTempo', 'inputSenderName', 'inputBankName', 'inputQuota', 'inputPrice', 'inputTanggalBayar', 'inputDesc', 'Type', 'all_transaction']],
            'class store' => ['head', 'post', 'head.class.store', fn () => [], fn () => [
                'inputType' => DB::table('class_types')->value('id'), 'inputTeacher' => User::where('role', 'teacher')->value('id'),
            ], ['inputType', 'inputTeacher']],
            'course store' => ['head', 'post', 'head.class.course.store', fn () => [], fn () => ['inputName' => 'Junk Course', 'inputPrice' => 1], ['inputName', 'inputPrice']],
            'class teacher store' => ['head', 'post', 'head.class.teacher.store', fn () => [], fn () => [
                'classId' => DB::table('class_transactions')->value('id'), 'teacherId' => User::where('role', 'teacher')->value('id'),
            ], ['classId', 'teacherId']],
            'class student store' => ['head', 'post', 'head.class.student.store', fn () => [], fn () => [
                'classId' => DB::table('class_transactions')->value('id'), 'studentId' => DB::table('students')->value('id'),
            ], ['classId', 'studentId']],
            'class level' => ['head', 'post', 'head.class.level', fn () => [], fn () => ['classId' => DB::table('class_transactions')->value('id')], ['classId']],
            'class level store' => ['head', 'post', 'head.class.level.store', fn () => [], fn () => ['classId' => DB::table('class_transactions')->value('id')], ['classId', 'return_url']],
            'freeze price' => ['head', 'post', 'head.class.freeze.update', fn () => [DB::table('class_transactions')->value('id')], fn () => ['inputPrice' => 1], ['inputPrice', 'return_url']],
            'class type edit' => ['head', 'post', 'head.class-type.edit', fn () => [], fn () => ['typeID' => DB::table('class_types')->value('id')], ['typeID']],
            'class type update' => ['head', 'post', 'head.class-type.update', fn () => [], fn () => ['typeID' => DB::table('class_types')->value('id'), 'inputPrice' => 1], ['typeID', 'inputPrice']],
            'class type destroy' => ['head', 'post', 'head.class-type.destroy', fn () => [], fn () => ['typeID' => 999999], ['typeID']],
            'schedule store' => ['head', 'post', 'head.schedule.store', fn () => [DB::table('class_transactions')->value('id')], fn () => ['dateTime' => '2030-01-01 10:00'], ['dateTime']],
            'schedule multiple store' => ['head', 'post', 'head.schedule.multiple.store', fn () => [], fn () => [
                'classId' => DB::table('class_transactions')->value('id'), 'dateTime' => '2030-01-01 10:00', 'ScheduleLoop' => 1,
            ], ['classId', 'dateTime', 'ScheduleLoop']],
            'schedule update' => ['head', 'post', 'head.schedule.update', fn () => [DB::table('schedules')->value('id')], fn () => ['dateTime' => '2030-01-01 10:00'], ['dateTime']],
            'stock store' => ['head', 'post', 'head.stock.store', fn () => [], fn () => ['inputName' => 'Junk', 'inputSize' => 'M', 'inputQty' => 1], ['inputName', 'inputSize', 'inputQty']],
            'stock update' => ['head', 'post', 'head.stock.update', fn () => [DB::table('stocks')->value('id')], fn () => ['inputName' => 'Junk', 'inputSize' => 'M', 'inputQty' => 1], ['inputName', 'inputSize', 'inputQty', 'return_url']],
            'staff attendance' => ['head', 'post', 'head.attendance.update', fn () => [DB::table('schedules')->value('id')], fn () => [
                'student_id' => [DB::table('students')->value('id')], 'check' => ['off'], 'keterangan' => ['Sick'], 'notes' => [''],
            ], ['student_id', 'check', 'keterangan', 'notes']],
            'teacher attendance' => ['teacher', 'post', 'getAbsen', fn () => [self::teacherSchedule()], fn () => [
                'student_id' => [DB::table('students')->value('id')], 'check' => ['off'], 'keterangan' => ['Sick'], 'notes' => [''],
            ], ['student_id', 'check', 'keterangan', 'notes']],
            'staff stock report' => ['head', 'post', 'head.report.stock.print', fn () => [], fn () => ['start_date' => '2026-01-01', 'end_date' => '2026-01-31'], ['start_date', 'end_date']],
            'staff active student report' => ['head', 'post', 'head.report.active-student.print', fn () => [], fn () => [], ['class']],
            'finance stock report' => ['finance', 'post', 'financeStockPrintReport', fn () => [], fn () => ['start_date' => '2026-01-01', 'end_date' => '2026-01-31'], ['start_date', 'end_date']],
            'finance student report' => ['finance', 'post', 'financeStudentReport', fn () => [], fn () => [], ['status', 'class']],
            'teacher add schedule' => ['teacher', 'post', 'addScheduleClassTeacher', fn () => [self::teacherClass()], fn () => ['dateTime' => '2030-01-01 10:00'], ['dateTime']],
            'teacher update schedule' => ['teacher', 'post', 'updateScheduleClassTeacher', fn () => [], fn () => ['scheduleId' => self::teacherSchedule(), 'dateTime' => '2030-01-01 10:00'], ['scheduleId', 'dateTime']],
            'teacher multiple schedule' => ['teacher', 'post', 'addMultipleScheduleClassTeacher', fn () => [], fn () => ['classId' => self::teacherClass(), 'dateTime' => '2030-01-01 10:00', 'ScheduleLoop' => 1], ['classId', 'dateTime', 'ScheduleLoop']],
            'teacher update schedule page' => ['teacher', 'get', 'viewUpdateScheduleClassTeacher', fn () => [], fn () => ['scheduleId' => self::teacherSchedule()], ['scheduleId']],
            'rule add' => ['head', 'post', 'RulesAdd', fn () => [], fn () => ['inputLanguage' => 'id', 'content' => '<p>x</p>'], ['inputLanguage', 'content']],
            'rule update' => ['head', 'post', 'RulesUpdate', fn () => [DB::table('rules')->value('id')], fn () => ['inputLanguage' => 'id', 'content' => '<p>x</p>'], ['inputLanguage', 'content']],
            'profile' => ['head', 'post', 'change-profile', fn () => [], fn () => ['email' => 'head@gmail.com', 'address' => 'Jl', 'phone' => '081234567890'], ['email', 'address', 'phone']],
            'password' => ['head', 'post', 'change-password', fn () => [], fn () => ['new_password' => 'longenough1', 'confirm_password' => 'longenough1'], ['new_password', 'confirm_password']],
            'login' => ['guest', 'post', 'do-login', fn () => [], fn () => ['email' => 'head@gmail.com', 'password' => 'wrong-password'], ['email', 'password']],
            'forgot password' => ['guest', 'post', 'check-email', fn () => [], fn () => ['email' => 'head@gmail.com'], ['email']],
            'reset password' => ['guest', 'post', 'reset-password', fn () => ['sometoken'], fn () => [
                'email' => 'head@gmail.com', 'password' => 'longenough1', 'password_confirmation' => 'longenough1',
            ], ['email', 'password', 'password_confirmation']],
            // Task 16: the routes the coverage guard found without a case
            'admin add' => ['head', 'post', 'AdminAdd', fn () => [], fn () => [
                'inputName' => 'Junk', 'inputEmail' => 'junk-admin@example.com', 'inputDate_of_Birth' => '1990-01-01', 'inputAddress' => 'Jl', 'inputPhone' => '081234567890',
            ], ['inputName', 'inputEmail', 'inputDate_of_Birth', 'inputAddress', 'inputPhone']],
            'admin update' => ['head', 'post', 'headAdminUpdate', fn () => [User::where('role', 'admin')->value('id')], fn () => [
                'inputName' => 'Junk', 'inputEmail' => 'junk-admin@example.com', 'inputDate_of_Birth' => '1990-01-01', 'inputAddress' => 'Jl', 'inputBonus' => 0, 'inputPhone' => '081234567890',
            ], ['inputName', 'inputEmail', 'inputDate_of_Birth', 'inputAddress', 'inputBonus', 'inputPhone']],
            'teacher store' => ['head', 'post', 'head.teacher.store', fn () => [], fn () => [
                'inputName' => 'Junk', 'inputEmail' => 'junk-teacher@example.com', 'inputDate_of_Birth' => '1990-01-01', 'inputAddress' => 'Jl', 'inputPhone' => '081234567890',
            ], ['inputName', 'inputEmail', 'inputDate_of_Birth', 'inputAddress', 'inputPhone']],
            'teacher update' => ['head', 'post', 'head.teacher.update', fn () => [User::where('role', 'teacher')->value('id')], fn () => [
                'inputName' => 'Junk', 'inputEmail' => 'junk-teacher@example.com', 'inputDate_of_Birth' => '1990-01-01', 'inputAddress' => 'Jl', 'inputBonus' => 0, 'inputPhone' => '081234567890',
            ], ['inputName', 'inputEmail', 'inputDate_of_Birth', 'inputAddress', 'inputBonus', 'inputPhone']],
            'finance account store' => ['head', 'post', 'head.finance.store', fn () => [], fn () => [
                'inputName' => 'Junk', 'inputEmail' => 'junk-finance@example.com', 'inputDate_of_Birth' => '1990-01-01', 'inputAddress' => 'Jl', 'inputPhone' => '081234567890',
            ], ['inputName', 'inputEmail', 'inputDate_of_Birth', 'inputAddress', 'inputPhone']],
            'finance account update' => ['head', 'post', 'head.finance.update', fn () => [User::where('role', 'finance')->value('id')], fn () => [
                'inputName' => 'Junk', 'inputEmail' => 'junk-finance@example.com', 'inputDate_of_Birth' => '1990-01-01', 'inputAddress' => 'Jl', 'inputBonus' => 0, 'inputPhone' => '081234567890',
            ], ['inputName', 'inputEmail', 'inputDate_of_Birth', 'inputAddress', 'inputBonus', 'inputPhone']],
            'buyer buy' => ['buyer', 'post', 'buying', fn () => [DB::table('stocks')->value('id')], fn () => ['name' => 'Junk', 'qty' => 1], ['name', 'qty', 'return_url']],
            'finance stock movement' => ['finance', 'post', 'makeReport', fn () => [DB::table('stocks')->value('id'), 'in'], fn () => ['in_out' => 1], ['in_out', 'return_url']],
            'finance mark paid' => ['finance', 'post', 'doPaidTransaction', fn () => [DB::table('transactions')->where('payment_status', 'Unpaid')->value('id')], fn () => [
                'datePaid' => '2026-01-10', 'inputBankName' => 'BCA', 'inputSenderName' => 'Ibu', 'inputQuota' => 3, 'Type' => 'Cash',
            ], ['datePaid', 'inputBankName', 'inputSenderName', 'inputQuota', 'Type', 'return_url']],
        ];
    }

    private static function teacherClass(): int
    {
        return (int) DB::table('mapping_class_teachers')
            ->where('user_id', User::where('email', 'teacher@gmail.com')->value('id'))->value('class_id');
    }

    private static function teacherSchedule(): int
    {
        return (int) DB::table('schedules')->where('class_id', self::teacherClass())
            ->whereNotIn('id', DB::table('header_absens')->pluck('schedules_id'))->value('id');
    }

    public function test_every_input_route_has_a_case(): void
    {
        $covered = array_column(self::cases(), 2);

        $missing = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($r) => array_diff($r->methods(), ['GET', 'HEAD']) !== [] && $r->getName() !== null)
            ->map(fn ($r) => $r->getName())
            ->reject(fn ($name) => str_starts_with($name, 'admin.') && Route::has('head.'.substr($name, 6))) // shared staff route; head.* is covered
            ->reject(fn ($name) => in_array($name, self::NO_INPUT, true) || in_array($name, $covered, true))
            ->values()->all();

        $this->assertSame([], $missing, 'Add a JunkInputTest case (or a NO_INPUT entry with a reason) for: '.implode(', ', $missing));
    }

    #[DataProvider('cases')]
    public function test_junk_input_is_never_a_500(string $role, string $method, string $route, Closure $params, Closure $payload, array $fields): void
    {
        $this->withoutMiddleware(ThrottleRequests::class);
        Mail::fake();
        $user = match ($role) {
            'guest' => null,
            'buyer' => User::factory()->create(['role' => 'buyer']),
            'teacher' => User::where('email', 'teacher@gmail.com')->firstOrFail(),
            default => User::where('role', $role)->firstOrFail(),
        };
        $url = route($route, $params());

        foreach ($fields as $field) {
            foreach (self::junk() as $junk) {
                $this->flushSession();
                $this->app['auth']->forgetGuards();
                $data = array_merge($payload(), [$field => $junk]);
                $response = ($user ? $this->actingAs($user) : $this)->call(strtoupper($method), $url, $data);

                $this->assertLessThan(500, $response->getStatusCode(), "{$route}: {$field} = ".json_encode($junk));
                // the update page redirects to the class list for a non-integer id, on purpose
                if ($method === 'get' && ! in_array($route, ['viewUpdateScheduleClassTeacher'], true)) {
                    $this->assertSame(200, $response->getStatusCode(), "{$route}: {$field} = ".json_encode($junk).' did not render');
                }
            }
        }
    }

    public function test_bad_filter_renders_the_page_instead_of_redirecting(): void
    {
        $this->actingAs(User::where('role', 'head')->firstOrFail())
            ->get(route('head.student.index', ['keyword' => ['x'], 'status' => 'nope']))
            ->assertOk();
    }

    public function test_array_filter_with_json_content_type_is_cleaned(): void
    {
        $head = User::where('role', 'head')->firstOrFail();
        $json = ['Content-Type' => 'application/json'];

        $this->actingAs($head)->get(route('head.student.index', ['keyword' => ['x']]), $json)->assertOk();
        $this->actingAs($head)->get(route('head.stock.index', ['search' => ['x']]), $json)->assertOk();
    }

    public function test_unknown_status_behaves_like_no_status_filter(): void
    {
        $head = User::where('role', 'head')->firstOrFail();

        $plain = $this->actingAs($head)->get(route('head.student.index'))->viewData('students')->pluck('id')->all();
        $junk = $this->actingAs($head)->get(route('head.student.index', ['status' => 'nope']))->viewData('students')->pluck('id')->all();

        $this->assertNotEmpty($plain);
        $this->assertSame($plain, $junk);
    }
}
