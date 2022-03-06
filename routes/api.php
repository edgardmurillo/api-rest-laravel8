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

Route::post('/register',[ApiController::class,'register']);
Route::get('/login',[ApiController::class,'login']);
Route::get('/users/{id}',[ApiController::class,'getUser'])->middleware('auth:api');
Route::get('/posts',[ApiController::class,'listPost']);
Route::post('/posts',[ApiController::class,'setPost'])->middleware('auth:api');
Route::get('/posts/{id}',[ApiController::class,'getPost']);
