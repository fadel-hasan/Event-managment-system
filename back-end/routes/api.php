<?php

use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\BookingController;
use App\Http\Controllers\API\CodeCheckController;
use App\Http\Controllers\API\ForgotPasswordController;
use App\Http\Controllers\API\ResetPasswordController;
use App\Http\Controllers\API\ReviewController;
use App\Http\Controllers\API\ServiceController;
use App\Http\Controllers\API\User\UserController;
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
Route::get('service/category/{id}',[ServiceController::class,'service_by_type']);
Route::middleware('auth:sanctum')->group(function (){
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('account/delete',[UserController::class,'delete_account']);
    Route::get('account/profile',[UserController::class,'show']);
    Route::put('account/edit',[UserController::class,'update']);
    Route::prefix('service')->group(function () {
        Route::post('add',[ServiceController::class,'store']);
        Route::post('detail/{id}',[ServiceController::class,'store_detail']);
        Route::get('category/food',[ServiceController::class,'get_category']);
        Route::post('favorite/{id}',[ServiceController::class,'favorite']);
        Route::post('unfavorite/{id}',[ServiceController::class,'unFavorite']);
        Route::get('myfavorite',[ServiceController::class,'myFavorites']);
        Route::get('{id}',[ServiceController::class,'service_detail']);
        Route::delete('delete/{id}',[ServiceController::class,'delete_service']);
        Route::post('addReview',[ReviewController::class,'review']);
        Route::post('booking',[BookingController::class,'booking']);
        Route::post('update/{id}',[ServiceController::class,'update']);
        Route::get('search/search', [ServiceController::class,'search']);
    });

    Route::get('payment/method',[BookingController::class,'paymentMethod']);
    Route::post('password/change',[ResetPasswordController::class,'change_password']);

    Route::get('user/services',[UserController::class,'services']);
    Route::get('user/booking',[BookingController::class,'booking_provider']);
    Route::get('booking/user',[BookingController::class,'booking_user']);

    Route::post('booking/accept/{id}',[BookingController::class,'accept']);
    Route::post('booking/reject/{id}',[BookingController::class,'reject']);
});




//Route::post('/forgot-password', [AuthController::class, 'forgetPassword']);
//Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::post('password/email',  ForgotPasswordController::class);
Route::post('password/code/check', CodeCheckController::class);
Route::post('password/reset', ResetPasswordController::class);


