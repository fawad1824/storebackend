<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MainController;
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
Route::post('/auth/register', [AuthController::class, 'createUser']);
Route::post('/auth/login', [AuthController::class, 'loginUser']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [MainController::class, 'userCheck']);
});


// category
Route::get('/category',[MainController::class,'Category']);
Route::post('/category',[MainController::class,'AddCategory']);

// products
Route::get('/product',[MainController::class,'Product']);
Route::get('/product/{name}',[MainController::class,'GetSingleProduct']);
Route::post('/product',[MainController::class,'AddProduct']);
Route::post('/productdeleted',[MainController::class,'deleteProduct']);

// Orders
Route::get('/orders',[MainController::class,'Orders']);
Route::post('/orders',[MainController::class,'AddOrders']);
