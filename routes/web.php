<?php

use App\Http\Controllers\admin\AdminClassScheduleController;
use App\Http\Controllers\admin\AdminClassTransactionController;
use App\Http\Controllers\admin\AdminController;
use App\Http\Controllers\admin\AdminReportController;
use App\Http\Controllers\admin\AdminStockController;
use App\Http\Controllers\admin\AdminStudentController;
use App\Http\Controllers\admin\AdminTeacherController;
use App\Http\Controllers\admin\AdminTransactionController;
use App\Http\Controllers\auth\LoginController;
use App\Http\Controllers\finance\FinanceController;
use App\Http\Controllers\finance\FinanceStockController;
use App\Http\Controllers\finance\FinanceTransactionController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\head\HeadAdminController;
use App\Http\Controllers\head\HeadClassController;
use App\Http\Controllers\head\HeadController;
use App\Http\Controllers\head\HeadFinanceController;
use App\Http\Controllers\head\HeadReportController;
use App\Http\Controllers\head\HeadStockController;
use App\Http\Controllers\head\HeadStudentController;
use App\Http\Controllers\head\HeadTeacherController;
use App\Http\Controllers\head\HeadTransactionController;
use App\Http\Controllers\head\HeadRuleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\teacher\TeacherClassController;
use App\Http\Controllers\teacher\TeacherController;
use App\Http\Controllers\BuyerController;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\head\HeadClassScheduleController;
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

Route::get('/',function (){
    if(!Auth::check()){
        return to_route('login');
    } else {
        return redirect()->to('/'.Auth::user()->role);
    }
});

//buyer
Route::middleware(['authLogin'])->prefix('buyer')->group(function(){
    Route::get('/', [BuyerController::class,'index'])->name('buyer');
    Route::get('/sorting/{value}/{type}', [BuyerController::class,'sorting'])->name('buyerSorting');
    Route::get('/buy/{id}', [BuyerController::class,'buyingPage'])->name('buyingItem');
    Route::post('/buy/{id}', [BuyerController::class,'buying'])->name('buying');
});

//login
Route::get('/login', [LoginController::class,'index'])->name('login');
Route::post('/login', [LoginController::class,'doLogin'])->name('do-login');

//admin
Route::prefix('admin')->middleware(['admin'])->group(function(){
    Route::get('/', [AdminController::class,'index'])->name('admin');
    Route::post('/class/search', [AdminClassTransactionController::class,'search'])->name('adminSearchClass');

    Route::get('/view/class/freeze', [AdminClassTransactionController::class,'viewClassFreeze'])->name('adminClassFreezeView');
    Route::get('/detail/class/freeze/{id}', [AdminClassTransactionController::class,'detailClassFreeze'])->name('adminDetailClassFreeze');

    Route::get('/view/class', [AdminClassTransactionController::class,'viewClass'])->name('adminClassView');
    Route::get('/view/class/sorting/{value}/{type}', [AdminClassTransactionController::class,'viewClassSorting'])->name('viewClassSorting');

    Route::get('/detail/class/reset/quota/{id}', [AdminClassTransactionController::class,'resetQuota'])->name('ResetQuota');
    Route::get('/detail/class/{id}', [AdminClassTransactionController::class,'detailClass'])->name('adminDetailClass');
    Route::get('/class/active', [AdminClassTransactionController::class,'active'])->name('adminActiveClassPage');
    Route::get('/class/non/active', [AdminClassTransactionController::class,'nonActive'])->name('adminNonActiveClassPage');

    Route::get('/class/add/course', [AdminClassTransactionController::class,'addCoursePage'])->name('adminCourseAddPage');
    Route::post('/class/add/course', [AdminClassTransactionController::class,'addCourse'])->name('adminCourseAdd');

    Route::get('/class/type', [AdminController::class,'indexType'])->name('adminClassTypePage');
    Route::post('/class/type/view', [AdminController::class,'viewUpdateType'])->name('adminViewChangeTypeClass');
    Route::post('/class/type/delete', [AdminController::class,'DeleteType'])->name('adminDeleteTypeClass');
    Route::post('/class/type/update', [AdminController::class,'updateType'])->name('adminChangeTypeClass');

    Route::post('/class/delete/{id}', [AdminClassTransactionController::class,'delete'])->name('adminDeleteClass');
    Route::post('/level/class', [AdminClassTransactionController::class,'levelUp'])->name('levelUp');
    Route::post('/level/class/student', [AdminClassTransactionController::class,'levelUpStudent'])->name('levelUpStudent');
    Route::get('/view/add/teacher/class/{id}', [AdminClassTransactionController::class,'viewaddTeacher'])->name('viewaddTeacherClass');
    Route::get('/view/add/student/class/{id}', [AdminClassTransactionController::class,'viewaddStudent'])->name('viewaddStudentClass');
    Route::post('/add/teacher/class', [AdminClassTransactionController::class,'addTeacher'])->name('addTeacherClass');
    Route::post('/add/student/class', [AdminClassTransactionController::class,'addStudent'])->name('addStudentClass');
    Route::get('/reset/class/{id}',[AdminClassTransactionController::class,'resetClass'])->name('resetClass'); // baru

    Route::get('/delete/TeacherClass/{teacher}/{class}',[AdminClassTransactionController::class,'deleteTeacher'])->name("classDeleteTeacher");

    Route::post('/generate-transaction/StudentClass/{student}/{class}',[AdminClassTransactionController::class,'generateTransactionStudent'])->name("classGenerateTransactionStudent");
    Route::get('/delete/StudentClass/{student}/{class}',[AdminClassTransactionController::class,'deleteStudent'])->name("classDeleteStudent");
    Route::get('/class/add', [AdminClassTransactionController::class,'insertPage'])->name('adminClassAddPage');
    Route::post('/class/add', [AdminClassTransactionController::class,'insert'])->name('adminClassAdd');
    Route::post('/class/non/active/{class}', [AdminClassTransactionController::class,'ChangeStatus'])->name('changeStatusClassAdmin');

    Route::get('/view/schedule/class/{id}', [AdminClassScheduleController::class,'viewSchedule'])->name('viewScheduleClass');

    Route::get('/delete/Schedule/class/{id}/{classId}',[AdminClassScheduleController::class,'deleteScheduleClass'])->name('deleteSchedule'); // baru

    // Route::post('/view/add/schedule/class',[AdminClassScheduleController::class,'viewaddScheduleClass'])->name('viewaddScheduleClass');
    Route::get('/view/add/schedule/class/{id}', [AdminClassScheduleController::class,'viewaddScheduleClass'])->name('adminViewAddScheduleClass');

    Route::get('/view/update/schedule/class/{id}',[AdminClassScheduleController::class,'viewUpdateScheduleClass'])->name('viewUpdateScheduleClass');

    Route::post('/add/MultipleSchedule/class',[AdminClassScheduleController::class,'addMultipleSchedule'])->name('addMultipleScheduleClass');// baru
    Route::post('/add/schedule/class/{id}',[AdminClassScheduleController::class,'addSchedule'])->name('addScheduleClass');
    Route::post('/update/schedule/class/{id}',[AdminClassScheduleController::class,'updateSchedule'])->name('updateScheduleClass');
    Route::get('/view/addMultipleSchedule/class', [AdminClassScheduleController::class,'viewAddMultipleScheduleClass'])->name('adminViewAddMultipleScheduleClass');// baru

    Route::get('/student/view', [AdminStudentController::class,'adminStudentView'])->name('adminStudentView');
    Route::get('/student/sorting/{value}/{type}', [AdminStudentController::class,'adminStudentViewSorting'])->name('adminStudentViewSorting');
    Route::get('/student/form', [AdminStudentController::class,'viewStudentForm'])->name('adminStudentForm');

    Route::post('/student/form', [AdminStudentController::class,'adminStudentFormSubmit'])->name('adminStudentForm');

    Route::post('/student/search', [AdminStudentController::class,'search'])->name('adminStudentSearch');
    Route::post('/student/change/{student}', [AdminStudentController::class,'ChangeNonactive'])->name('adminStudentChange');
    Route::get('/student/active', [AdminStudentController::class,'active'])->name('adminStudentActive');
    Route::get('/student/non/active', [AdminStudentController::class,'nonActive'])->name('adminStudentNonActive');
    Route::post('/student/delete/{studentId}', [AdminStudentController::class,'deleteStudent'])->name('adminStudentDelete');
    Route::get('/student/detail/{studentId}', [AdminStudentController::class,'detailStudent'])->name('adminStudentDetail');
    Route::post('/student/update', [AdminStudentController::class, 'update'])->name('adminUpdateStudent');

    Route::get('/student/class/add/{id}', [AdminStudentController::class,'insertClassPage'])->name('adminStudentClassAddPage');
    Route::post('/student/class/add/{classId}/{id}', [AdminStudentController::class,'insertClass'])->name('adminStudentClassInsertPage');

    Route::get('/teacher/view', [AdminTeacherController::class,'adminTeacherView'])->name('adminTeacherView');
    Route::get('/teacher/form', [AdminTeacherController::class,'adminTeacherForm'])->name('adminTeacherForm');
    Route::post('/teacher/form', [AdminTeacherController::class,'adminTeacherFormSubmit'])->name('adminTeacherForm');
    Route::get('/teacher/delete/{teacher}', [AdminTeacherController::class,'delete'])->name('adminTeacherDelete');
    Route::post('/teacher/detail/{teacher}', [AdminTeacherController::class,'detailTeacher'])->name('adminTeacherDetail');
    Route::get('/teacher/search', [AdminTeacherController::class,'search'])->name('adminTeacherSearch');
    Route::get('/teacher/update/{teacher}', [AdminTeacherController::class,'updatePage'])->name('adminTeacherUpdatePage');
    Route::post('/teacher/update/{teacher}', [AdminTeacherController::class,'update'])->name('adminTeacherUpdate');
    Route::post('/teacher/update/{teacher}/{replaceTeacherID}', [AdminTeacherController::class,'replace'])->name('adminTeacherReplace');
    

    Route::get('/stock', [AdminStockController::class,'index'])->name('adminStockPage');
    Route::get('/stock/sorting/{value}/{sort}', [AdminStockController::class,'adminStock'])->name('adminStockViewSorting');

    Route::get('/report',[AdminReportController::class,'index'])->name('adminReportPage');
    Route::post('/report/{header}',[AdminReportController::class,'print'])->name('adminPrintReport');

    Route::get('/transaction', [AdminTransactionController::class,'index'])->name('adminTransactionPage');
    Route::get('/transaction/add', [AdminTransactionController::class,'addTransaction'])->name('addTransaction');
    Route::post('/transaction/insert', [AdminTransactionController::class,'insertTransaction'])->name('insertTransaction');
    Route::get('/transaction/search/{sort}', [AdminTransactionController::class,'searchTransaction'])->name('adminSearchTransaction');
    Route::get('/transaction/view/paid/{transactionId}', [AdminTransactionController::class,'viewPaidTransaction'])->name('adminPaidTransaction');
    Route::post('/transaction/submit/paid/{transactionId}', [AdminTransactionController::class,'submitPaidTransaction'])->name('adminSubmitPaidTransaction');
    Route::get('/transaction/detail/{transaction}', [AdminTransactionController::class,'detailTransaction'])->name('adminDetailTransaction');
    Route::get('/transaction/{id}', [AdminTransactionController::class,'updatePage'])->name('adminUpdateTransaction');
    Route::post('/transaction/update/{transaction}', [AdminTransactionController::class,'update'])->name('adminUpdate');
    Route::get('/transaction/get-price', [AdminTransactionController::class,'getPrice'])->name('getPrice');

    Route::get('/transaction/sorting/{value}/{type}', [AdminTransactionController::class,'adminTransactionSorting'])->name('adminTransactionSorting');

    Route::get('/report/class',[AdminReportController::class,'classAttendence'])->name('adminClassReport');
    Route::post('/report/class/{header}/{teacher}',[AdminReportController::class,'printClassAttendence'])->name('adminClassPrintReport');
    Route::get('/report/active/student',[AdminReportController::class,'printActiveStudentPage'])->name('adminPrintActiveStudentPage');
    Route::post('/report/active/student',[AdminReportController::class,'printActiveStudent'])->name('adminPrintActiveStudent');

});

//head
Route::prefix('head')->middleware(['head'])->group(function(){
    Route::get('/', [HeadController::class,'index'])->name('head');

    Route::get('/view/class/freeze', [HeadClassController::class,'viewClassFreeze'])->name('headClassFreezeView');
    Route::get('/detail/class/freeze/{id}', [HeadClassController::class,'detailClassFreeze'])->name('headDetailClassFreeze');
    Route::get('/update/class/freeze/{id}', [HeadClassController::class,'updateClassFreezePage'])->name('headUpdateClassFreezePage');
    Route::post('/update/class/freeze/{id}', [HeadClassController::class,'updateClassFreeze'])->name('headUpdateClassFreeze');

    Route::get('/class', [HeadClassController::class,'index'])->name('headClassPage');
    Route::get('/class/active', [HeadClassController::class,'active'])->name('activeClassPage');
    Route::get('/class/non/active', [HeadClassController::class,'nonActive'])->name('nonactiveClassPage');
    Route::post('/class/non/active/{class}', [HeadClassController::class,'ChangeStatus'])->name('changeStatusClass');
    Route::get('/class/add', [HeadClassController::class,'insertPage'])->name('headClassAddPage');
    Route::post('/class/add', [HeadClassController::class,'insert'])->name('ClassAdd');
    Route::post('/class/search', [HeadClassController::class,'search'])->name('searchClass');
    Route::post('/class/delete/{class}', [HeadClassController::class,'delete'])->name('deleteClass');
    Route::get('/view/class/sorting/{value}/{type}', [HeadClassController::class,'sorting'])->name('sorting');

    Route::get('/class/add/course', [HeadClassController::class,'addCoursePage'])->name('headCourseAddPage');
    Route::post('/class/add/course', [HeadClassController::class,'addCourse'])->name('headCourseAdd');

    Route::get('/class/type', [HeadClassController::class,'indexType'])->name('headClassTypePage');
    Route::post('/class/type/view', [HeadClassController::class,'viewUpdateType'])->name('headViewChangeTypeClass');
    Route::post('/class/type/delete', [HeadClassController::class,'DeleteType'])->name('headDeleteTypeClass');
    Route::post('/class/type/update', [HeadClassController::class,'updateType'])->name('headChangeTypeClass');

    Route::post('/level/class', [HeadClassController::class,'levelUp'])->name('headLevelUp');
    Route::post('/class/delete/{id}', [HeadClassController::class,'delete'])->name('headDeleteClass');
    Route::post('/level/class/student', [HeadClassController::class,'levelUpStudent'])->name('headLevelUpStudent');

    Route::get('/detail/class/reset/quota/{id}', [HeadClassController::class,'resetQuota'])->name('headResetQuota');
    Route::get('/detail/class/{id}', [HeadClassController::class,'detailClass'])->name('headDetailClass');
    Route::get('/view/add/teacher/class/{id}', [HeadClassController::class,'viewaddTeacher'])->name('headViewaddTeacherClass');
    Route::get('/view/add/student/class/{id}', [HeadClassController::class,'viewaddStudent'])->name('headViewaddStudentClass');
    Route::get('/delete/TeacherClass/{teacher}/{class}',[HeadClassController::class,'deleteTeacher'])->name("headClassDeleteTeacher");
    Route::get('/delete/StudentClass/{student}/{class}',[HeadClassController::class,'deleteStudent'])->name("headClassDeleteStudent");
    Route::post('/generate-transaction/StudentClass/{student}/{class}',[HeadClassController::class,'generateTransactionStudent'])->name("headClassGenerateTransactionStudent");
    Route::post('/add/teacher/class', [HeadClassController::class,'addTeacher'])->name('headAddTeacherClass');
    Route::post('/add/student/class', [HeadClassController::class,'addStudent'])->name('headAddStudentClass');
    Route::get('/reset/class/{id}',[HeadClassController::class,'resetClass'])->name('headResetClass');

    Route::get('/view/schedule/class/{classId}', [HeadClassScheduleController::class,'viewSchedule'])->name('headViewScheduleClass');

    Route::get('/delete/Schedule/class/{id}/{classId}',[HeadClassScheduleController::class,'deleteScheduleClass'])->name('headDeleteSchedule');

    Route::get('/view/add/schedule/class/{id}',[HeadClassScheduleController::class,'viewaddScheduleClass'])->name('headViewaddScheduleClass');

    Route::post('/view/update/schedule/class',[HeadClassScheduleController::class,'viewUpdateScheduleClass'])->name('headViewUpdateScheduleClass');
    Route::post('/add/MultipleSchedule/class',[HeadClassScheduleController::class,'addMultipleSchedule'])->name('headAddMultipleScheduleClass');
    Route::post('/add/schedule/class',[HeadClassScheduleController::class,'addSchedule'])->name('headAddScheduleClass');
    Route::post('/update/schedule/class',[HeadClassScheduleController::class,'updateSchedule'])->name('headUpdateScheduleClass');
    Route::get('/view/addMultipleSchedule/class/{id}',[HeadClassScheduleController::class,'viewAddMultipleScheduleClass'])->name('headViewaddMultipleScheduleClass');// baru

    Route::get('/student', [HeadStudentController::class,'index'])->name('headStudentPage');

    Route::get('/student/active', [HeadStudentController::class,'active'])->name('activeStudentPage');
    Route::get('/student/non/active', [HeadStudentController::class,'nonActive'])->name('nonactiveStudentPage');
    Route::post('/student/non/active/{student}', [HeadStudentController::class,'ChangeNonactive'])->name('nonactiveStudent');
    Route::get('/student/add', [HeadStudentController::class,'insertPage'])->name('headStudentAddPage');

    Route::get('/student/class/add/{id}', [HeadStudentController::class,'insertClassPage'])->name('headStudentClassAddPage');
    Route::post('/student/class/add/{classId}/{id}', [HeadStudentController::class,'insertClass'])->name('headStudentClassInsertPage');

    Route::post('/student/add', [HeadStudentController::class,'insert'])->name('StudentAdd');
    Route::post('/student/search', [HeadStudentController::class,'search'])->name('searchStudent');

    Route::get('/student/detail/{id}', [HeadStudentController::class,'detailStudent'])->name('detailStudent');
    Route::post('/student/delete/{student}', [HeadStudentController::class,'deleteStudent'])->name('deleteStudent');
    Route::get('/student/sorting/{value}/{type}', [HeadStudentController::class,'sorting'])->name('sortingStudent');

    Route::post('/student/update', [HeadStudentController::class, 'update'])->name('updateStudent');

    Route::get('/teacher', [HeadTeacherController::class,'index'])->name('headTeacherPage');
    Route::get('/teacher/add', [HeadTeacherController::class,'insertPage'])->name('headTeacherAddPage');
    Route::post('/teacher/add', [HeadTeacherController::class,'insert'])->name('TeacherAdd');
    Route::post('/teacher/search', [HeadTeacherController::class,'search'])->name('searchTeacher');
    Route::get('/teacher/delete/{teacher}', [HeadTeacherController::class,'delete'])->name('TeacherDelete');
    Route::get('/teacher/update/{teacher}', [HeadTeacherController::class,'updatePage'])->name('TeacherUpdatePage');
    Route::post('/teacher/update/{teacher}', [HeadTeacherController::class,'update'])->name('TeacherUpdate');
    Route::post('/teacher/update/{teacher}/{replaceTeacherID}', [HeadTeacherController::class,'replace'])->name('TeacherReplace');

    Route::get('/finance', [HeadFinanceController::class,'index'])->name('headFinancePage');
    Route::get('/finance/add', [HeadFinanceController::class,'insertPage'])->name('headFinanceAddPage');
    Route::post('/finance/add', [HeadFinanceController::class,'insert'])->name('FinanceAdd');
    Route::post('/finance/delete/{user}', [HeadFinanceController::class,'delete'])->name('FinanceDelete');
    Route::post('/finance/search', [HeadFinanceController::class,'search'])->name('searchFinance');
    Route::get('/finance/update/{user}', [HeadFinanceController::class,'updatePage'])->name('headFinanceUpdatePage');
    Route::post('/finance/update/{user}', [HeadFinanceController::class,'update'])->name('headFinanceUpdate');

    Route::get('/admin', [HeadAdminController::class,'index'])->name('headAdminPage');
    Route::get('/admin/add', [HeadAdminController::class,'insertPage'])->name('headAdminAddPage');
    Route::post('/admin/add', [HeadAdminController::class,'insert'])->name('AdminAdd');
    Route::post('/admin/delete/{user}', [HeadAdminController::class,'delete'])->name('AdminDelete');
    Route::post('/admin/search', [HeadAdminController::class,'search'])->name('searchAdmin');
    Route::get('/admin/update/{user}', [HeadAdminController::class,'updatePage'])->name('headAdminUpdatePage');
    Route::post('/admin/update/{user}', [HeadAdminController::class,'update'])->name('headAdminUpdate');

    Route::get('/transaction', [HeadTransactionController::class,'index'])->name('headTransactionPage');
    Route::get('/transaction/sorting/{column}/{type}', [HeadTransactionController::class,'sorting'])->name('headTransactionSorting');
    Route::get('/transaction/add', [HeadTransactionController::class,'addTransaction'])->name('headAddTransactionPage');
    Route::post('/transaction/add', [HeadTransactionController::class,'insertTransaction'])->name('headAddTransaction');
    Route::get('/transaction/search', [HeadTransactionController::class,'index'])->name('headSearchTransaction');
    Route::get('/transaction/{id}', [HeadTransactionController::class,'updatePage'])->name('updateTransaction');
    Route::post('/transaction/update/{transaction}', [HeadTransactionController::class,'update'])->name('update');
    Route::get('/transaction/detail/{transaction}', [HeadTransactionController::class,'detailTransaction'])->name('detailTransaction');
    Route::post('/transaction/delete/{transaction}', [HeadTransactionController::class,'delete'])->name('deleteTransaction');
    
    Route::get('/transaction/get-price', [HeadTransactionController::class,'getPrice'])->name('getPrice');

    Route::get('/stock', [HeadStockController::class,'index'])->name('headStockPage');
    Route::get('/stock/add', [HeadStockController::class,'insertPage'])->name('headStockAddPage');
    Route::post('/stock/add', [HeadStockController::class,'insert'])->name('StockAdd');
    Route::get('/stock/{stock}', [HeadStockController::class,'updatePage'])->name('headStockUpdatePage');
    Route::post('/stock/update/{stock}', [HeadStockController::class,'update'])->name('StockUpdate');
    Route::post('/stock/delete/{stock}', [HeadStockController::class,'delete'])->name('stockDelete');
    Route::post('/stock/search', [HeadStockController::class,'search'])->name('searchStock');
    Route::get('/stock/sorting/{value}/{sort}', [HeadStockController::class,'sorting'])->name('headStockViewSorting');

    Route::get('/report/class',[HeadReportController::class,'classAttendence'])->name('headClassReport');
    Route::post('/report/class/{header}/{teacher}',[HeadReportController::class,'printClassAttendence'])->name('headClassPrintReport');
    Route::get('/report/active/student',[HeadReportController::class,'printActiveStudentPage'])->name('headPrintActiveStudentPage');
    Route::post('/report/active/student',[HeadReportController::class,'printActiveStudent'])->name('headPrintActiveStudent');
    Route::get('/report/stock',[HeadReportController::class,'stock'])->name('headStockReport');
    Route::post('/report/stock',[HeadReportController::class,'printStock'])->name('headStockPrintReport');

    Route::post('/view/class/absen/{class}', [HeadClassController::class,'viewAbsen'])->name('headViewAbsen');
    Route::post('/view/class/getabsen/{schedule}', [HeadClassController::class,'getAbsen'])->name('headGetAbsen');

    Route::get('/report/teacher',[HeadReportController::class,'reportTeacherPage'])->name('headTeacherReportPage');
    Route::post('/report/teacher/{month}',[HeadReportController::class,'reportTeacher'])->name('headTeacherReport');

    Route::get('/report/rule',[HeadRuleController::class,'index'])->name('Rules');
    Route::get('/report/rule/add',[HeadRuleController::class,'insertPage'])->name('RulesAddPage');
    Route::post('/report/rule/add',[HeadRuleController::class,'insert'])->name('RulesAdd');
    Route::get('/report/rule/update/{rules}',[HeadRuleController::class,'updatePage'])->name('RulesUpdatePage');
    Route::post('/report/rule/update/{rules}',[HeadRuleController::class,'update'])->name('RulesUpdate');
    Route::get('/report/rule/delete/{rules}',[HeadRuleController::class,'delete'])->name('RulesDelete');
});

// teacher
Route::prefix('teacher')->middleware(['teacher'])->group(function(){
    Route::get('/', [TeacherController::class,'index'])->name('teacher');
    Route::get('/view/class', [TeacherClassController::class,'index'])->name('viewClass');

    Route::post('/view/class/{id}', [TeacherClassController::class,'viewDetail'])->name('viewDetailTeacher');
    Route::post('/view/class/absen/{id}', [TeacherController::class,'viewAbsen'])->name('viewAbsen');
    Route::post('/view/class/getabsen/{schedule}', [TeacherController::class,'getAbsen'])->name('getAbsen');

    Route::get('/view/schedule/class/{id}', [TeacherClassController::class,'viewSchedule'])->name('viewScheduleClassTeacher');

    Route::get('/delete/Schedule/class/{id}/{classId}', [TeacherClassController::class,'deleteScheduleClass'])->name('deleteScheduleTeacher');
    Route::get('/view/update/schedule/class', [TeacherClassController::class,'viewUpdateScheduleClass'])->name('viewUpdateScheduleClassTeacher');

    Route::post('/update/schedule/class', [TeacherClassController::class,'updateSchedule'])->name('updateScheduleClassTeacher');

    Route::get('/view/add/schedule/class/{id}', [TeacherClassController::class,'viewaddScheduleClass'])->name('viewaddScheduleClass');

    Route::get('/view/addMultipleSchedule/class/{id}', [TeacherClassController::class,'viewAddMultipleScheduleClass'])->name('viewaddMultipleScheduleClass');

    Route::post('/add/schedule/class/{id}', [TeacherClassController::class,'addSchedule'])->name('addScheduleClassTeacher');

    Route::post('/add/MultipleSchedule/class', [TeacherClassController::class,'addMultipleSchedule'])->name('addMultipleScheduleClassTeacher');

    Route::get('/view/class/schedule/{id}', [TeacherClassController::class,'viewClassSchedule'])->name('viewAllScheduleTeacher');
});

//finance
Route::prefix('finance')->middleware(['finance'])->group(function(){
    Route::get('/', [FinanceController::class,'index'])->name('finance');

    Route::get('/transaction/sorting/{column}', [FinanceTransactionController::class,'sorting'])->name('financeTransactionSorting');

    Route::get('/in/{stock}', [FinanceStockController::class,'in'])->name('in');
    Route::post('/out/{stock}', [FinanceStockController::class,'out'])->name('out');
    Route::post('/stock/report/{stock}/{type}', [FinanceStockController::class,'report'])->name('makeReport');
    Route::get('/stock/sorting/{value}/{sort}', [FinanceStockController::class,'financeStock'])->name('financeStockViewSorting');

    Route::get('/transaction', [FinanceTransactionController::class,'index'])->name('financeTransaction');
    Route::get('/transaction/search', [FinanceTransactionController::class,'search'])->name('searchTransaction');
    Route::get('/transaction/paid/{transaction}', [FinanceTransactionController::class,'viewPaidTransaction'])->name('paidTransaction');
    Route::post('/transaction/do-paid/{trans}', [FinanceTransactionController::class,'submitPaidTransaction'])->name('doPaidTransaction');

    Route::get('/report/stock',[FinanceStockController::class,'stock'])->name('financeStockReport');
    Route::post('/report/stock',[FinanceStockController::class,'printStock'])->name('financeStockPrintReport');

    Route::get('/report/teacher',[FinanceController::class,'reportTeacherPage'])->name('financeTeacherReportPage');
    Route::post('/report/teacher/{month}',[FinanceController::class,'reportTeacher'])->name('financeTeacherReport');
    Route::get('/report/finance/student',[FinanceController::class,'reportStudentPage'])->name('financeStudentReportPage');
    Route::post('/report/finance/student',[FinanceController::class,'reportStudent'])->name('financeStudentReport');
});

Route::middleware(['authLogin'])->group(function(){
    Route::get('/logout', [LoginController::class,'logout'])->name('logout');

    //profile
    Route::get('/profile',[ProfileController::class,'changeProfilePage'])->name('change-profile-page');
    Route::post('/profile/{user}',[ProfileController::class,'changeProfile'])->name('change-profile');

    Route::get('/password',[ProfileController::class,'changePasswordPage'])->name('change-password-page');
    Route::post('/password/{user}',[ProfileController::class,'changePassword'])->name('change-password');

});

//forgot password
Route::get('/forgot/password',[ForgotPasswordController::class,'index'])->name('email-page');
Route::post('/forgot/password',[ForgotPasswordController::class,'checkEmail'])->name('check-email');
Route::get('/expired',[ForgotPasswordController::class,'index'])->name('expired-page');

Route::get('/reset/password/{token}',[ForgotPasswordController::class,'resetPasswordPage'])->name('reset-password-page');
Route::post('/reset/password/{token}',[ForgotPasswordController::class,'resetPassword'])->name('reset-password');
