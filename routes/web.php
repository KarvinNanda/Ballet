<?php

use App\Http\Controllers\admin\ClassTransactionController;
use App\Http\Controllers\admin\StudentController;
use App\Http\Controllers\admin\TeacherController;

use App\Http\Controllers\auth\LoginController;

use App\Http\Controllers\head\HeadController;
use App\Http\Controllers\head\HeadTeacherController;
use App\Http\Controllers\head\HeadStudentController;
use App\Http\Controllers\head\HeadClassController;
use App\Http\Controllers\head\HeadAdminController;
use App\Http\Controllers\head\HeadStockController;
use App\Http\Controllers\head\HeadTransactionController;
use App\Http\Controllers\admin\ClassScheduleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\admin\AdminController;

use Illuminate\Support\Facades\Route;

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

//login
Route::get('/login', [LoginController::class,'index'])->name('login');
Route::post('/login', [LoginController::class,'doLogin'])->name('do-login');
Route::get('/logout', [LoginController::class,'logout'])->name('logout');



//admin
Route::prefix('admin')->group(function(){
    Route::get('/', [AdminController::class,'index'])->name('adminPage');
    Route::get('/view/class', [ClassTransactionController::class,'viewClass'])->name('adminClassView');
    Route::post('/detail/class', [ClassTransactionController::class,'detailClass'])->name('adminDetailClass');
    Route::get('/view/add/teacher/class/{id}', [ClassTransactionController::class,'viewaddTeacher'])->name('viewaddTeacherClass');
    Route::get('/view/add/student/class/{id}', [ClassTransactionController::class,'viewaddStudent'])->name('viewaddStudentClass');
    Route::post('/add/teacher/class', [ClassTransactionController::class,'addTeacher'])->name('addTeacherClass');
    Route::post('/add/student/class', [ClassTransactionController::class,'addStudent'])->name('addStudentClass');
    Route::post('/view/schedule/class', [ClassScheduleController::class,'viewSchedule'])->name('viewScheduleClass');
    Route::get('/delete/Schedule/class/{id}/{classId}',[\App\Http\Controllers\admin\ClassScheduleController::class,'deleteScheduleClass'])->name('deleteSchedule'); // baru
    Route::get('/reset/class/{id}',[\App\Http\Controllers\admin\ClassTransactionController::class,'resetClass'])->name('resetClass'); // baru

    Route::post('/view/add/schedule/class',[ClassScheduleController::class,'viewaddScheduleClass'])->name('viewaddScheduleClass');
    Route::post('/add/MultipleSchedule/class',[\App\Http\Controllers\admin\ClassScheduleController::class,'addMultipleSchedule'])->name('addMultipleScheduleClass');// baru

    Route::post('/add/schedule/class',[ClassScheduleController::class,'addSchedule'])->name('addScheduleClass');
    Route::post('/view/addMultipleSchedule/class',[\App\Http\Controllers\admin\ClassScheduleController::class,'viewAddMultipleScheduleClass'])->name('viewaddMultipleScheduleClass');// baru
    Route::get('/view/absence',[ClassScheduleController::class,'viewAbsen'])->name('viewAbsen');
    Route::post('/get/absence/{schedule}',[ClassScheduleController::class,'getAbsen'])->name('getAbsen');

    Route::get('/delete/TeacherClass/{teacher}/{class}',[ClassTransactionController::class,'deleteTeacher'])->name("classDeleteTeacher");
    Route::get('/delete/StudentClass/{student}/{class}',[ClassTransactionController::class,'deleteStudent'])->name("classDeleteStudent");

    Route::get('/student/view', [StudentController::class,'adminStudentView'])->name('adminStudentView');
    Route::get('/student/form', [StudentController::class,'viewStudentForm'])->name('adminStudentForm');;
    Route::post('/student/form', [StudentController::class,'adminStudentFormSubmit'])->name('adminStudentForm');
    Route::post('/student/search', [StudentController::class,'search'])->name('adminStudentSearch');
    Route::post('/student/change/{student}', [StudentController::class,'ChangeNonactive'])->name('adminStudentChange');
    Route::get('/student/active', [StudentController::class,'active'])->name('adminStudentActive');
    Route::get('/student/non/active', [StudentController::class,'nonActive'])->name('adminStudentNonActive');

    Route::get('/teacher/view', [TeacherController::class,'adminTeacherView'])->name('adminTeacherView');
    Route::get('/teacher/form', [TeacherController::class,'adminTeacherForm'])->name('adminTeacherForm');
    Route::post('/teacher/form', [TeacherController::class,'adminTeacherFormSubmit'])->name('adminTeacherForm');
    Route::post('/teacher/delete/{teacher}', [TeacherController::class,'delete'])->name('adminTeacherDelete');
    Route::post('/teacher/detail/{teacher}', [TeacherController::class,'detail'])->name('adminTeacherDetail');
    Route::post('/teacher/search', [TeacherController::class,'search'])->name('adminTeacherSearch');
});




//Route::post('/classdeleteTeacher/{teacher}/{class}',[ClassTransactionController::class,'deleteTeacher'])->name("classDeleteTeacher");

//student
Route::get('/adminStudentView', [StudentController::class,'adminStudentView'])->name('adminStudentView');
Route::get('/adminStudentForm', [StudentController::class,'viewStudentForm'])->name('adminStudentForm');;
Route::post('/adminStudentForm', [StudentController::class,'adminStudentFormSubmit'])->name('adminStudentForm');
Route::post('/adminStudentSearch', [StudentController::class,'search'])->name('adminStudentSearch');
Route::post('/adminStudentChange/{student}', [StudentController::class,'ChangeNonactive'])->name('adminStudentChange');
Route::get('/adminStudentActive', [StudentController::class,'active'])->name('adminStudentActive');
Route::get('/adminStudentNonActive', [StudentController::class,'nonActive'])->name('adminStudentNonActive');

//teacher
Route::get('/adminTeacherView', [TeacherController::class,'adminTeacherView'])->name('adminTeacherView');
Route::get('/adminTeacherForm', [TeacherController::class,'adminTeacherForm'])->name('adminTeacherForm');
Route::post('/adminTeacherForm', [TeacherController::class,'adminTeacherFormSubmit'])->name('adminTeacherForm');
Route::post('/adminTeacherDelete/{teacher}', [TeacherController::class,'delete'])->name('adminTeacherDelete');
Route::post('/adminTeacherSearch', [TeacherController::class,'search'])->name('adminTeacherSearch');

//head
Route::prefix('head')->group(function(){
    Route::get('/', [HeadController::class,'index'])->name('head');

    Route::get('/class', [HeadClassController::class,'index'])->name('headClassPage');
    Route::get('/class/active', [HeadClassController::class,'active'])->name('activeClassPage');
    Route::get('/class/non/active', [HeadClassController::class,'nonActive'])->name('nonactiveClassPage');
    Route::post('/class/non/active/{class}', [HeadClassController::class,'ChangeStatus'])->name('changeStatusClass');
    Route::get('/class/add', [HeadClassController::class,'insertPage'])->name('headClassAddPage');
    Route::post('/class/add', [HeadClassController::class,'insert'])->name('ClassAdd');
    Route::post('/class/search', [HeadClassController::class,'search'])->name('searchClass');

    Route::get('/student', [HeadStudentController::class,'index'])->name('headStudentPage');
    Route::get('/student/active', [HeadStudentController::class,'active'])->name('activeStudentPage');
    Route::get('/student/non/active', [HeadStudentController::class,'nonActive'])->name('nonactiveStudentPage');
    Route::post('/student/non/active/{student}', [HeadStudentController::class,'ChangeNonactive'])->name('nonactiveStudent');
    Route::get('/student/add', [HeadStudentController::class,'insertPage'])->name('headStudentAddPage');
    Route::post('/student/add', [HeadStudentController::class,'insert'])->name('StudentAdd');
    Route::post('/student/search', [HeadStudentController::class,'search'])->name('searchStudent');

    Route::get('/teacher', [HeadTeacherController::class,'index'])->name('headTeacherPage');
    Route::get('/teacher/add', [HeadTeacherController::class,'insertPage'])->name('headTeacherAddPage');
    Route::post('/teacher/add', [HeadTeacherController::class,'insert'])->name('TeacherAdd');
    Route::post('/teacher/search', [HeadTeacherController::class,'search'])->name('searchTeacher');
    Route::post('/teacher/delete/{teacher}', [HeadTeacherController::class,'delete'])->name('TeacherDelete');

    Route::get('/admin', [HeadAdminController::class,'index'])->name('headAdminPage');
    Route::get('/admin/add', [HeadAdminController::class,'insertPage'])->name('headAdminAddPage');
    Route::post('/admin/add', [HeadAdminController::class,'insert'])->name('AdminAdd');
    Route::post('/admin/delete/{user}', [HeadAdminController::class,'delete'])->name('AdminDelete');
    Route::post('/admin/search', [HeadAdminController::class,'search'])->name('searchAdmin');

    Route::get('/transaction', [HeadTransactionController::class,'index'])->name('headTransactionPage');
    Route::post('/transaction/{id}', [HeadTransactionController::class,'updatePage'])->name('updateTransaction');
    Route::post('/transaction/update/{transaction}', [HeadTransactionController::class,'update'])->name('update');
    Route::post('/transaction/search', [HeadTransactionController::class,'search'])->name('searchTransaction');

    Route::get('/stock', [HeadStockController::class,'index'])->name('headStockPage');
    Route::get('/stock/add', [HeadStockController::class,'insertPage'])->name('headStockAddPage');
    Route::post('/stock/add', [HeadStockController::class,'insert'])->name('StockAdd');
    Route::get('/stock/{stock}', [HeadStockController::class,'updatePage'])->name('headStockUpdatePage');
    Route::post('/stock/update/{stock}', [HeadStockController::class,'update'])->name('StockUpdate');
    Route::post('/stock/delete/{stock}', [HeadStockController::class,'delete'])->name('stockDelete');
    Route::post('/stock/search', [HeadStockController::class,'search'])->name('searchStock');
});

//profile
Route::get('/profile',[ProfileController::class,'changeProfilePage'])->name('change-profile-page');
Route::post('/profile/{user}',[ProfileController::class,'changeProfile'])->name('change-profile');

Route::get('/password',[ProfileController::class,'changePasswordPage'])->name('change-password-page');
Route::post('/password/{user}',[ProfileController::class,'changePassword'])->name('change-password');
