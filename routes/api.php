<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\API\User\ChangePasswordController;
use App\Http\Controllers\API\User\BookController;
use App\Http\Controllers\API\User\CategoryController;
use App\Http\Controllers\API\User\LocationController;


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

Route::post("register", [RegisterController::class, "register"]);
Route::post("login", [LoginController::class, "login"]);
Route::post("forgot-password", [ForgotPasswordController::class, "forgotPassword"])->name('password.reset');
Route::post("reset-password", [ForgotPasswordController::class, "resetPassword"]);

Route::group([
    "middleware" => ["auth:sanctum"]
], function(){
    //profile 
    Route::get("profile", [ProfileController::class, "profile"]);
    Route::get("logout", [LogoutController::class, "logout"]);
    Route::put("changePassword", [ChangePasswordController::class, "changePassword"]);
});

Route::get('/getAllBooks', [BookController::class, 'getAllProducts']);
Route::get('/getBookDetail/{id}', [BookController::class, 'getBookDetails']);
Route::get('/getAllCategories', [CategoryController::class, 'index']);
Route::get('/getBookByCategory/{category_id}', [BookController::class, 'getBookByCategory']);
Route::get('/books/search', [BookController::class, 'search']);

// Loacation
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/address/store', [LocationController::class, 'store']);
    Route::put('/address/update/{id}', [LocationController::class, 'update']);
    Route::delete('/address/destroy/{id}', [LocationController::class, 'destroy']);
});



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


