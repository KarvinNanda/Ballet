<?php

// Routes shared by admin and head. Loaded twice from web.php:
// inside prefix('admin')->name('admin.') and prefix('head')->name('head.').
// Head-only routes stay in web.php. Head-only actions here are guarded by Gates in the controller.

use App\Http\Controllers\staff\AttendanceController;
use App\Http\Controllers\staff\ClassController;
use App\Http\Controllers\staff\ClassScheduleController;
use App\Http\Controllers\staff\ClassTypeController;
use App\Http\Controllers\staff\FinanceAccountController;
use App\Http\Controllers\staff\ReportController;
use App\Http\Controllers\staff\StockController;
use App\Http\Controllers\staff\StudentController;
use App\Http\Controllers\staff\TeacherController;
use App\Http\Controllers\staff\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
Route::get('/stock/sorting/{column}/{direction}', [StockController::class, 'sort'])->name('stock.sort');
Route::get('/stock/add', [StockController::class, 'create'])->name('stock.create');
Route::post('/stock/add', [StockController::class, 'store'])->name('stock.store');
Route::get('/stock/update/{stock}', [StockController::class, 'edit'])->name('stock.edit');
Route::post('/stock/update/{stock}', [StockController::class, 'update'])->name('stock.update');
Route::post('/stock/delete/{stock}', [StockController::class, 'destroy'])->name('stock.destroy');

Route::get('/teacher', [TeacherController::class, 'index'])->name('teacher.index');
Route::get('/teacher/add', [TeacherController::class, 'create'])->name('teacher.create');
Route::post('/teacher/add', [TeacherController::class, 'store'])->middleware('throttle:account-create')->name('teacher.store');
Route::get('/teacher/update/{teacher}', [TeacherController::class, 'edit'])->name('teacher.edit');
Route::post('/teacher/update/{teacher}', [TeacherController::class, 'update'])->name('teacher.update');
Route::post('/teacher/delete/{teacher}', [TeacherController::class, 'destroy'])->name('teacher.destroy');
Route::get('/teacher/switch/{teacher}', [TeacherController::class, 'switch'])->name('teacher.switch');
Route::post('/teacher/replace/{teacher}/{replacement}', [TeacherController::class, 'replace'])->name('teacher.replace');

Route::get('/report/class', [ReportController::class, 'classAttendance'])->name('report.class');
Route::post('/report/class/{header}/{teacher}', [ReportController::class, 'printClassAttendance'])->name('report.class.print');
Route::get('/report/active/student', [ReportController::class, 'activeStudent'])->name('report.active-student');
Route::post('/report/active/student', [ReportController::class, 'printActiveStudent'])->name('report.active-student.print');
Route::get('/report/stock', [ReportController::class, 'stock'])->name('report.stock');
Route::post('/report/stock', [ReportController::class, 'printStock'])->name('report.stock.print');
Route::get('/report/teacher', [ReportController::class, 'teacher'])->name('report.teacher');
Route::post('/report/teacher/{month}', [ReportController::class, 'printTeacher'])->name('report.teacher.print');

Route::get('/finance', [FinanceAccountController::class, 'index'])->name('finance.index');
Route::get('/finance/add', [FinanceAccountController::class, 'create'])->name('finance.create');
Route::post('/finance/add', [FinanceAccountController::class, 'store'])->middleware('throttle:account-create')->name('finance.store');
Route::get('/finance/update/{user}', [FinanceAccountController::class, 'edit'])->name('finance.edit');
Route::post('/finance/update/{user}', [FinanceAccountController::class, 'update'])->name('finance.update');
Route::post('/finance/delete/{user}', [FinanceAccountController::class, 'destroy'])->name('finance.destroy');

Route::get('/transaction', [TransactionController::class, 'index'])->name('transaction.index');
Route::get('/transaction/sorting/{column}/{direction}', [TransactionController::class, 'sort'])->name('transaction.sort');
Route::get('/transaction/add', [TransactionController::class, 'create'])->name('transaction.create');
Route::post('/transaction/add', [TransactionController::class, 'store'])->name('transaction.store');
Route::get('/transaction/get-price', [TransactionController::class, 'price'])->name('transaction.price');
Route::get('/transaction/detail/{transaction}', [TransactionController::class, 'show'])->name('transaction.show');
Route::get('/transaction/{transaction}', [TransactionController::class, 'edit'])->whereNumber('transaction')->name('transaction.edit');
Route::post('/transaction/update/{transaction}', [TransactionController::class, 'update'])->name('transaction.update');
Route::post('/transaction/delete/{transaction}', [TransactionController::class, 'destroy'])->name('transaction.destroy');

Route::get('/student', [StudentController::class, 'index'])->name('student.index');
Route::get('/student/active', fn () => redirect(staff_route('student.index', ['status' => 'aktif'])))->name('student.active');
Route::get('/student/non/active', fn () => redirect(staff_route('student.index', ['status' => 'non-aktif'])))->name('student.inactive');
Route::get('/student/sorting/{column}/{direction}', [StudentController::class, 'sort'])->name('student.sort');
Route::get('/student/add', [StudentController::class, 'create'])->name('student.create');
Route::post('/student/add', [StudentController::class, 'store'])->name('student.store');
Route::get('/student/detail/{student}', [StudentController::class, 'show'])->name('student.show');
Route::post('/student/update/{student}', [StudentController::class, 'update'])->name('student.update');
Route::post('/student/status/{student}', [StudentController::class, 'toggleStatus'])->name('student.status');
Route::post('/student/delete/{student}', [StudentController::class, 'destroy'])->name('student.destroy');
Route::get('/student/class/add/{student}', [StudentController::class, 'classCreate'])->name('student.class.create');
Route::post('/student/class/add/{class}/{student}', [StudentController::class, 'classStore'])->name('student.class.store');

Route::get('/class', [ClassController::class, 'index'])->name('class.index');
Route::get('/class/active', fn () => redirect(staff_route('class.index', ['status' => 'aktif'])))->name('class.active');
Route::get('/class/non/active', fn () => redirect(staff_route('class.index', ['status' => 'non-aktif'])))->name('class.inactive');
Route::get('/class/sorting/{column}/{direction}', [ClassController::class, 'sort'])->name('class.sort');
Route::get('/class/add', [ClassController::class, 'create'])->name('class.create');
Route::post('/class/add', [ClassController::class, 'store'])->name('class.store');
Route::get('/class/add/course', [ClassController::class, 'createCourse'])->name('class.course.create');
Route::post('/class/add/course', [ClassController::class, 'storeCourse'])->name('class.course.store');
Route::get('/class/detail/{class}', [ClassController::class, 'show'])->whereNumber('class')->name('class.show');
Route::post('/class/status/{class}', [ClassController::class, 'toggleStatus'])->whereNumber('class')->name('class.status');
Route::post('/class/delete/{class}', [ClassController::class, 'destroy'])->whereNumber('class')->name('class.destroy');
Route::post('/class/reset/quota/{class}', [ClassController::class, 'resetQuota'])->whereNumber('class')->name('class.reset-quota');
Route::post('/class/reset/{class}', [ClassController::class, 'resetClass'])->whereNumber('class')->name('class.reset');
Route::get('/class/teacher/add/{class}', [ClassController::class, 'teacherCreate'])->whereNumber('class')->name('class.teacher.create');
Route::post('/class/teacher/add', [ClassController::class, 'teacherStore'])->name('class.teacher.store');
Route::post('/class/teacher/delete/{teacher}/{class}', [ClassController::class, 'teacherDestroy'])->whereNumber(['teacher', 'class'])->name('class.teacher.destroy');
Route::get('/class/student/add/{class}', [ClassController::class, 'studentCreate'])->whereNumber('class')->name('class.student.create');
Route::post('/class/student/add', [ClassController::class, 'studentStore'])->name('class.student.store');
Route::post('/class/student/delete/{student}/{class}', [ClassController::class, 'studentDestroy'])->whereNumber(['student', 'class'])->name('class.student.destroy');
Route::post('/class/student/generate-transaction/{student}/{class}', [ClassController::class, 'generateTransaction'])->whereNumber(['student', 'class'])->name('class.student.generate-transaction');
Route::post('/class/level', [ClassController::class, 'levelUp'])->name('class.level');
Route::post('/class/level/student', [ClassController::class, 'levelUpStudent'])->name('class.level.store');

Route::get('/class/freeze', [ClassController::class, 'freezeIndex'])->name('class.freeze.index');
Route::get('/class/freeze/{class}', [ClassController::class, 'freezeShow'])->whereNumber('class')->name('class.freeze.show');
Route::get('/class/freeze/{class}/update', [ClassController::class, 'freezeEdit'])->whereNumber('class')->name('class.freeze.edit');
Route::post('/class/freeze/{class}/update', [ClassController::class, 'freezeUpdate'])->whereNumber('class')->name('class.freeze.update');

Route::get('/class/{class}/schedule', [ClassScheduleController::class, 'index'])->whereNumber('class')->name('schedule.index');
Route::get('/class/{class}/schedule/add', [ClassScheduleController::class, 'create'])->whereNumber('class')->name('schedule.create');
Route::post('/class/{class}/schedule/add', [ClassScheduleController::class, 'store'])->whereNumber('class')->name('schedule.store');
Route::get('/class/schedule/multiple/{class?}', [ClassScheduleController::class, 'createMultiple'])->name('schedule.multiple.create');
Route::post('/class/schedule/multiple', [ClassScheduleController::class, 'storeMultiple'])->name('schedule.multiple.store');
Route::get('/schedule/{schedule}/update', [ClassScheduleController::class, 'edit'])->name('schedule.edit');
Route::post('/schedule/{schedule}/update', [ClassScheduleController::class, 'update'])->name('schedule.update');
Route::post('/schedule/{schedule}/delete', [ClassScheduleController::class, 'destroy'])->name('schedule.destroy');
Route::get('/schedule/{schedule}/attendance', [AttendanceController::class, 'edit'])->name('attendance.edit');
Route::post('/schedule/{schedule}/attendance', [AttendanceController::class, 'update'])->name('attendance.update');

Route::get('/class/type', [ClassTypeController::class, 'index'])->name('class-type.index');
Route::post('/class/type/view', [ClassTypeController::class, 'edit'])->name('class-type.edit');
Route::post('/class/type/update', [ClassTypeController::class, 'update'])->name('class-type.update');
Route::post('/class/type/delete', [ClassTypeController::class, 'destroy'])->name('class-type.destroy');

// Old class GET paths (bookmarks, links in old emails). Named so staff_route() knows the prefix.
Route::get('/view/class/freeze', fn () => redirect(staff_route('class.freeze.index'), 301))->name('class.legacy.freeze-index');
Route::get('/view/class/sorting/{column}/{direction}', fn ($column, $direction) => redirect(staff_route('class.sort', [$column, $direction]), 301))->name('class.legacy.sort');
Route::get('/detail/class/freeze/{id}', fn ($id) => redirect(staff_route('class.freeze.show', $id), 301))->whereNumber('id')->name('class.legacy.freeze-show');
Route::get('/detail/class/{id}', fn ($id) => redirect(staff_route('class.show', $id), 301))->whereNumber('id')->name('class.legacy.show');
Route::get('/view/add/teacher/class/{id}', fn ($id) => redirect(staff_route('class.teacher.create', $id), 301))->whereNumber('id')->name('class.legacy.teacher-create');
Route::get('/view/add/student/class/{id}', fn ($id) => redirect(staff_route('class.student.create', $id), 301))->whereNumber('id')->name('class.legacy.student-create');
