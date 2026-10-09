<?php

use App\Http\Controllers\auth\LoginController;
use App\Http\Controllers\finance\FinanceController;
use App\Http\Controllers\finance\FinanceStockController;
use App\Http\Controllers\finance\FinanceTransactionController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\head\HeadAdminController;
use App\Http\Controllers\staff\DashboardController;
use App\Http\Controllers\head\HeadRuleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\teacher\TeacherClassController;
use App\Http\Controllers\teacher\TeacherController;
use App\Http\Controllers\BuyerController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function (\Illuminate\Http\Request $request) {
    $role = (string) $request->user()?->role;

    if (Route::has($role)) {
        return to_route($role);
    }

    // Guest, or an account without a dashboard: send to login instead of looping on "/".
    if ($request->user()) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('login')->withErrors(['email' => 'Akun belum punya akses. Hubungi admin.']);
    }

    return to_route('login');
});

//buyer
Route::middleware(['role:buyer', 'throttle:writes'])->prefix('buyer')->group(function(){
    Route::get('/', [BuyerController::class,'index'])->name('buyer');
    Route::get('/sorting/{value}/{type}', [BuyerController::class,'sorting'])->name('buyerSorting');
    Route::get('/buy/{stock}', [BuyerController::class,'buyingPage'])->name('buyingItem');
    Route::post('/buy/{stock}', [BuyerController::class,'buying'])->name('buying');
});

//login
Route::get('/login', [LoginController::class,'index'])->name('login');
Route::post('/login', [LoginController::class,'doLogin'])->name('do-login');

//admin
Route::prefix('admin')->middleware(['role:admin', 'throttle:writes'])->group(function(){
    Route::get('/', [DashboardController::class,'index'])->name('admin');
    Route::name('admin.')->group(base_path('routes/staff.php'));
    Route::permanentRedirect('/student/view', '/admin/student');
    Route::permanentRedirect('/student/form', '/admin/student/add');
    Route::permanentRedirect('/view/class', '/admin/class');
    // Old parameterless schedule GET (took the class as ?classId=).
    Route::get('/view/addMultipleSchedule/class', fn (\Illuminate\Http\Request $r) => redirect(route('admin.schedule.multiple.create', filter_var($r->query('classId'), FILTER_VALIDATE_INT) ?: null), 301))->name('admin.schedule.legacy.multiple-create');

    Route::permanentRedirect('/teacher/view', '/admin/teacher');
    Route::permanentRedirect('/teacher/form', '/admin/teacher/add');
    Route::permanentRedirect('/teacher/search', '/admin/teacher');
    

});

//head
Route::prefix('head')->middleware(['role:head', 'throttle:writes'])->group(function(){
    Route::get('/', [DashboardController::class,'index'])->name('head');
    Route::name('head.')->group(base_path('routes/staff.php'));
    Route::permanentRedirect('/transaction/search', '/head/transaction');
    Route::get('/update/class/freeze/{id}', fn ($id) => redirect(route('head.class.freeze.edit', $id), 301))->whereNumber('id');

    

    Route::get('/admin', [HeadAdminController::class,'index'])->name('headAdminPage');
    Route::get('/admin/add', [HeadAdminController::class,'insertPage'])->name('headAdminAddPage');
    Route::post('/admin/add', [HeadAdminController::class,'insert'])->middleware('throttle:account-create')->name('AdminAdd');
    Route::post('/admin/delete/{user}', [HeadAdminController::class,'delete'])->name('AdminDelete');
    Route::post('/admin/search', [HeadAdminController::class,'search'])->name('searchAdmin');
    Route::get('/admin/update/{user}', [HeadAdminController::class,'updatePage'])->name('headAdminUpdatePage');
    Route::post('/admin/update/{user}', [HeadAdminController::class,'update'])->name('headAdminUpdate');

    Route::get('/stock/{stock}', fn ($stock) => redirect("/head/stock/update/{$stock}", 301))->whereNumber('stock');

    Route::get('/report/rule',[HeadRuleController::class,'index'])->name('Rules');
    Route::get('/report/rule/add',[HeadRuleController::class,'insertPage'])->name('RulesAddPage');
    Route::post('/report/rule/add',[HeadRuleController::class,'insert'])->name('RulesAdd');
    Route::get('/report/rule/update/{rules}',[HeadRuleController::class,'updatePage'])->name('RulesUpdatePage');
    Route::post('/report/rule/update/{rules}',[HeadRuleController::class,'update'])->name('RulesUpdate');
    Route::post('/report/rule/delete/{rules}',[HeadRuleController::class,'delete'])->name('RulesDelete');
});

// teacher
Route::prefix('teacher')->middleware(['role:teacher', 'throttle:writes'])->group(function(){
    Route::get('/', [TeacherController::class,'index'])->name('teacher');
    Route::get('/view/class', [TeacherClassController::class,'index'])->name('viewClass');

    Route::post('/view/class/{id}', [TeacherClassController::class,'viewDetail'])->name('viewDetailTeacher');
    Route::post('/view/class/absen/{id}', [TeacherController::class,'viewAbsen'])->name('viewAbsen');
    Route::post('/view/class/getabsen/{schedule}', [TeacherController::class,'getAbsen'])->name('getAbsen');

    Route::get('/view/schedule/class/{id}', [TeacherClassController::class,'viewSchedule'])->name('viewScheduleClassTeacher');

    Route::post('/delete/Schedule/class/{id}/{classId}', [TeacherClassController::class,'deleteScheduleClass'])->name('deleteScheduleTeacher')->whereNumber(['id', 'classId']);
    Route::get('/view/update/schedule/class', [TeacherClassController::class,'viewUpdateScheduleClass'])->name('viewUpdateScheduleClassTeacher');

    Route::post('/update/schedule/class', [TeacherClassController::class,'updateSchedule'])->name('updateScheduleClassTeacher');

    Route::get('/view/add/schedule/class/{id}', [TeacherClassController::class,'viewaddScheduleClass'])->name('viewaddScheduleClass');

    Route::get('/view/addMultipleSchedule/class/{id}', [TeacherClassController::class,'viewAddMultipleScheduleClass'])->name('viewaddMultipleScheduleClass');

    Route::post('/add/schedule/class/{id}', [TeacherClassController::class,'addSchedule'])->name('addScheduleClassTeacher');

    Route::post('/add/MultipleSchedule/class', [TeacherClassController::class,'addMultipleSchedule'])->name('addMultipleScheduleClassTeacher');

    Route::get('/view/class/schedule/{id}', [TeacherClassController::class,'viewClassSchedule'])->name('viewAllScheduleTeacher');
});

//finance
Route::prefix('finance')->middleware(['role:finance', 'throttle:writes'])->group(function(){
    Route::get('/', [FinanceController::class,'index'])->name('finance');

    Route::get('/transaction/sorting/{column}', [FinanceTransactionController::class,'sorting'])->name('financeTransactionSorting');

    Route::get('/in/{stock}', [FinanceStockController::class,'in'])->name('in');
    Route::post('/stock/report/{stock}/{type}', [FinanceStockController::class,'report'])->whereIn('type', ['in', 'out'])->name('makeReport');
    Route::get('/stock/sorting/{value}/{sort}', [FinanceStockController::class,'financeStock'])->name('financeStockViewSorting');

    Route::get('/transaction', [FinanceTransactionController::class,'index'])->name('financeTransaction');
    Route::get('/transaction/search', [FinanceTransactionController::class,'search'])->name('searchTransaction');
    Route::get('/transaction/paid/{transaction}', [FinanceTransactionController::class,'viewPaidTransaction'])->name('paidTransaction');
    Route::post('/transaction/do-paid/{transaction}', [FinanceTransactionController::class,'submitPaidTransaction'])->name('doPaidTransaction');

    Route::get('/report/stock',[FinanceStockController::class,'stock'])->name('financeStockReport');
    Route::post('/report/stock',[FinanceStockController::class,'printStock'])->name('financeStockPrintReport');

    Route::get('/report/teacher',[FinanceController::class,'reportTeacherPage'])->name('financeTeacherReportPage');
    Route::post('/report/teacher/{month}',[FinanceController::class,'reportTeacher'])->name('financeTeacherReport');
    Route::get('/report/finance/student',[FinanceController::class,'reportStudentPage'])->name('financeStudentReportPage');
    Route::post('/report/finance/student',[FinanceController::class,'reportStudent'])->name('financeStudentReport');
});

Route::middleware(['authLogin', 'throttle:writes'])->group(function(){
    Route::post('/logout', [LoginController::class,'logout'])->name('logout');

    //profile
    Route::get('/profile',[ProfileController::class,'changeProfilePage'])->name('change-profile-page');
    Route::post('/profile',[ProfileController::class,'changeProfile'])->name('change-profile');

    Route::get('/password',[ProfileController::class,'changePasswordPage'])->name('change-password-page');
    Route::post('/password',[ProfileController::class,'changePassword'])->name('change-password');

});

//forgot password
Route::get('/forgot/password',[ForgotPasswordController::class,'index'])->name('email-page');
Route::post('/forgot/password',[ForgotPasswordController::class,'checkEmail'])->middleware('throttle:5,1')->name('check-email');
Route::get('/expired',[ForgotPasswordController::class,'expired'])->name('expired-page');

Route::get('/reset/password/{token}',[ForgotPasswordController::class,'resetPasswordPage'])->name('reset-password-page');
Route::post('/reset/password/{token}',[ForgotPasswordController::class,'resetPassword'])->middleware('throttle:5,1')->name('reset-password');
