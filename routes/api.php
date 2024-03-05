<?php

use App\Http\Controllers\Api\V1\AuthController;
use \App\Http\Controllers\Api\V1\NewsController;
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

Route::prefix('v1')->group(function() {
    Route::prefix('auth')->group(function() {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/refresh', [AuthController::class, 'refreshToken'])->middleware('jwtRefresh');
        Route::middleware('jwtAuth')->group(function() {
            Route::get('/logout', [AuthController::class, 'logout']);
            Route::get('/user', [AuthController::class, 'getUser']);
            Route::get('/test', [AuthController::class, 'test']);
            Route::put('/password', [AuthController::class, 'updateMyPassword']);
        });
    });

    Route::middleware('jwtAuth')->prefix('news')->group(function() {
       Route::get('/category', [NewsController::class, 'getCategory']);
       Route::get('/list', [NewsController::class, 'getNews']);
    });
});
