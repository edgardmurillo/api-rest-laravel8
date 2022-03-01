<?php

use App\Http\Controllers\ApiController;
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

Route::put('/register',[ApiController::class,'register']);
Route::post('/login',[ApiController::class,'login']);
Route::get('/detail',[ApiController::class,'detail'])->middleware('auth:api');
Route::get('/list-post',[ApiController::class,'listPost']);
Route::put('/set-post',[ApiController::class,'setPost'])->middleware('auth:api');
Route::get('/get-post/{id}',[ApiController::class,'getPost']);
