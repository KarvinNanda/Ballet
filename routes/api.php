<?php

use App\Http\Controllers\head\HeadStudentController;
use App\Http\Controllers\head\HeadTransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/class-suit', [HeadStudentController::class,'active']);
Route::get('/class-price-suit', [HeadStudentController::class,'active2']);
Route::get('/class-price-suit-non-freeze', [HeadStudentController::class,'active3']);
Route::get('/age-suit', [HeadStudentController::class,'nonActive']);

Route::get('/transaction-suit', [HeadTransactionController::class,'Suit']);
