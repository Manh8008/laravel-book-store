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
use App\Http\Controllers\API\User\CheckOutController;
use App\Http\Controllers\API\User\CommentController;
use App\Http\Controllers\API\Admin\BookAdminController;
use App\Http\Controllers\API\Admin\CatalogAdminController;
use App\Http\Controllers\API\Admin\OrderAdminController;
use App\Http\Controllers\API\Admin\LoginAdminController;
use App\Http\Controllers\API\Admin\ReviewPostController;
use App\Http\Controllers\API\Admin\CommentAdminController;
use App\Http\Controllers\API\Admin\BannerAdminController;
use App\Http\Middleware\CheckAdmin;
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
Route::post("verify-otp", [RegisterController::class, "verifyOtp"]);
Route::post("resend-otp", [RegisterController::class, "resendOtp"]);
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
    Route::post("/checkoutCOD", [CheckOutController::class, "checkoutCOD"]);
    Route::post("/checkout-vnpay", [CheckOutController::class, "vnpayPayment"]);
}); 
    Route::get("/vnpay-return", [CheckOutController::class, "vnpayReturn"]);
    // Lấy sách
    Route::get('/getAllBooks', [BookController::class, 'getAllProducts']);
    Route::get('/getNewBook', [BookController::class, 'getNewBook']);
    Route::get('/getBookDetail/{id}', [BookController::class, 'getBookDetails']);
    Route::get('/getBookByCategory/{category_id}', [BookController::class, 'getBookByCategory']);
    Route::get('/books/search', [BookController::class, 'search']);
    Route::get('/best-sellers', [BookController::class, 'getBestSellers']);
    Route::get('/getBooksOrderPriceDesc', [BookController::class, 'getBooksSortedByPriceDesc']);
    Route::get('/getBooksOrderPriceAsc', [BookController::class, 'getBooksSortedByPriceAsc']);
    Route::get('/filterByPrice', [BookController::class, 'filterByPrice']);
    Route::get('/comments/{id}', [CommentController::class, 'getCommentsByBook']);

    // Categỏy
    Route::get('/getAllCategories', [CategoryController::class, 'index']);
    Route::get('/categories/search', [CategoryController::class, 'search']);

    
// Loacation
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/address/store', [LocationController::class, 'store']);
        Route::put('/address/update/{id}', [LocationController::class, 'update']);
        Route::delete('/address/destroy/{id}', [LocationController::class, 'destroy']);
        Route::put('/address/defaultUpdate/{id}', [LocationController::class, 'defaultUpdate']);
        Route::get('getAddressesById/{id}', [LocationController::class, 'getAddressesById']);

        //comment
        Route::post("/addComment/{id}", [CommentController::class, "addComment"]);
        Route::delete('/deleteComment/{id}', [CommentController::class, 'deleteComment']);
    });
    // Admin 
    // Login
    Route::post("admin/login", [LoginAdminController::class, "loginAdmin"]);
    // Route::post("admin/register", [LoginAdminController::class, "registerAdmin"]);
    // Orderadmin
    Route::get('/search-orders', [OrderAdminController::class, 'searchOrders']);
    Route::get('/pending-orders', [OrderAdminController::class, 'getAllPendingOrders']);
    Route::get('/getAllOrder', [OrderAdminController::class, 'getAllOrder']);

    // Post
    Route::get('/getAllPost', [ReviewPostController::class, 'getAllPost']);
    Route::get('/getPostById/{id}', [ReviewPostController::class, 'getPostById']);

    // commnet
    Route::get('/getAllComment', [CommentAdminController::class, 'getAllComment']);
    Route::get('/getCommentsByBookId/{id}', [CommentAdminController::class, 'getCommentsByBookId']);



    Route::middleware(['auth:sanctum','checkAdmin'])->group(function () {
        // category
        Route::post("admin/storeCatalog", [CatalogAdminController::class, "store"]);
        Route::put("admin/updateCatalog/{id}", [CatalogAdminController::class, "update"]);
        Route::delete("admin/destroyCatalog/{id}", [CatalogAdminController::class, "destroy"]);

        // Books
        Route::post("admin/storeBook", [BookAdminController::class, "store"]);
        Route::put("admin/updateBook/{id}", [BookAdminController::class, "update"]);
        Route::delete("admin/deleteBook/{id}", [BookAdminController::class, "destroy"]);
        // Orders
        Route::put("admin/updateOrderStatus/{id}", [OrderAdminController::class, "updateOrderStatus"]);

        //Post
        Route::post("admin/storePost", [ReviewPostController::class, "store"]);
        Route::put("admin/updatePost/{id}", [ReviewPostController::class, "update"]);
        Route::delete("admin/deletePost/{id}", [ReviewPostController::class, "destroy"]);
        
        //Comment
        Route::delete("admin/deleteComment/{id}", [CommentAdminController::class, "deleteComment"]);

        // Banner
        Route::post("admin/storeBanner", [BannerAdminController::class, "store"]);
        Route::delete("admin/deleteBanner/{id}", [BannerAdminController::class, "destroy"]);

    });


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


