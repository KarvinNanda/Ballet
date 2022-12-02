<?php

use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\ClassTransactionController;
use \App\Http\Controllers\ScheduleController;
use \App\Http\Controllers\StudentController;
use \App\Http\Controllers\TeacherController;
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

Route::get('/', [ClassTransactionController::class,'adminPage'])->name('adminPage');
Route::get('/viewClass', [ClassTransactionController::class,'viewClass'])->name('adminClassView');
Route::get('/adminStudentForm', [StudentController::class,'viewStudentForm']);
Route::get('/adminTeacherForm', [TeacherController::class,'adminTeacherForm'])->name('adminTeacherForm');
Route::get('/adminTeacherDelete/{id}', [TeacherController::class,'deleteTeacher'])->name('adminTeacherDelete');
Route::post('/adminTeacherForm', [TeacherController::class,'adminTeacherFormSubmit'])->name('adminTeacherForm');
Route::get('/adminStudentView', [StudentController::class,'adminStudentView'])->name('adminStudentView');
Route::get('/adminTeacherView', [TeacherController::class,'adminTeacherView'])->name('adminTeacherView');
