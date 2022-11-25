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

Route::get('/', [ClassTransactionController::class,'adminPage']);
Route::get('/viewClass', [ClassTransactionController::class,'viewClass']);
Route::get('/viewStudentForm', [StudentController::class,'viewStudentForm']);
Route::get('/viewTeacherForm', [TeacherController::class,'viewTeacherForm']);

