<?php

use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\CodeCheckController;
use App\Http\Controllers\API\ForgotPasswordController;
use App\Http\Controllers\API\ResetPasswordController;
use App\Http\Controllers\API\User\UserController;
use App\Http\Controllers\ServiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/service/home',[ServiceController::class,'get_type_service']);
Route::middleware('auth:sanctum')->group(function (){
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('account/delete',[UserController::class,'delete_account']);
    Route::get('account/profile',[UserController::class,'show']);
    Route::put('account/edit',[UserController::class,'update']);
    Route::post('service/add',[ServiceController::class,'store']);
    Route::post('service/detail/{id}',[ServiceController::class,'store_detail']);
    Route::get('service/category/food',[ServiceController::class,'get_category']);
    Route::post('password/change',[ResetPasswordController::class,'change_password']);
    Route::post('service/favorite/{id}',[ServiceController::class,'favorite']);
    Route::post('service/unfavorite/{id}',[ServiceController::class,'unFavorite']);
    Route::get('service/myfavorite',[ServiceController::class,'myFavorites']);
});




//Route::post('/forgot-password', [AuthController::class, 'forgetPassword']);
//Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::post('password/email',  ForgotPasswordController::class);
Route::post('password/code/check', CodeCheckController::class);
Route::post('password/reset', ResetPasswordController::class);


