<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SettingController;
use Symfony\Component\HttpFoundation\Response;

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

Route::get('/', function () {
    return redirect('/admin', Response::HTTP_MOVED_PERMANENTLY);
});

Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->middleware('prevent.back.history')->name('login-form');
Route::post('/logout', [AuthController::class, 'logout'])->middleware(['auth:web', 'prevent.back.history'])->name('logout');

Route::middleware(['auth:web'])->prefix('admin')->group(function() {
    Route::get('/', [HomeController::class, 'index']);

    Route::post('/ajax/auth/check', [AuthController::class, 'onceAuthorize'])->middleware('prevent.back.history')->name('auth.check');

    Route::middleware('prevent.back.history')->prefix('profile')->group(function() {
        Route::get('/', [AuthController::class, 'getMyInfo'])->name('profile');
        Route::get('/edit', [AuthController::class, 'updateMyInfo'])->middleware('once.auth')->name('update.profile');
        Route::prefix('ajax')->group(function() {
            Route::put('/edit/password', [\App\Http\Controllers\Api\V1\AuthController::class, 'updateMyPassword'])->name('update.password');
            Route::put('/edit/nickname', [\App\Http\Controllers\Api\V1\AuthController::class, 'updateMyNickname'])->name('update.nickname');
        });
    });

    Route::prefix('news')->group(function() {
        Route::get('/list', [NewsController::class, 'newsList'])->name('news.list');
        Route::get('/write', [NewsController::class, 'newsWrite'])->name('news.write');
        Route::get('/category', [NewsController::class, 'category'])->name('news.category');
        Route::middleware('prevent.back.history')->prefix('ajax')->group(function() {
            Route::get('/list', [\App\Http\Controllers\Api\V1\NewsController::class, 'getNews'])->name('news.ajax.get.list');
            Route::get('/detail', [\App\Http\Controllers\Api\V1\NewsController::class, 'getNewsDetail'])->name('news.ajax.get.detail');
            Route::put('/detail', [\App\Http\Controllers\Api\V1\NewsController::class, 'updateNewsDetail'])->name('news.ajax.put.detail');
            Route::put('/all/use', [\App\Http\Controllers\Api\V1\NewsController::class, 'updateNewsAllUse'])->name('news.ajax.put.allUse');
            Route::put('/all/serviceDate', [\App\Http\Controllers\Api\V1\NewsController::class, 'updateNewsAllServiceDate'])->name('news.ajax.put.allServiceDate');
            Route::post('/write', [\App\Http\Controllers\Api\V1\NewsController::class, 'insertNews'])->name('news.ajax.post.write');
            Route::get('/category', [\App\Http\Controllers\Api\V1\NewsController::class, 'getCategory'])->name('news.ajax.get.category');
            Route::get('/tags', [\App\Http\Controllers\Api\V1\NewsController::class, 'getTags'])->name('news.ajax.get.tags');
            Route::post('/category', [\App\Http\Controllers\Api\V1\NewsController::class, 'insertClassification'])->name('news.ajax.post.classification');
            Route::put('/category', [\App\Http\Controllers\Api\V1\NewsController::class, 'updateClassification'])->name('news.ajax.put.classification');
        });
    });

    Route::prefix('user')->group(function() {
        Route::get('/list', [UserController::class, 'userList'])->name('user.list');
        Route::get('/detail/{no}', [UserController::class, 'userDetail']);
        Route::post('/register', [UserController::class, 'userRegister']);
        Route::put('/update', [UserController::class, 'userUpdate']);
        Route::delete('/delete', [UserController::class, 'userDelete']);
    });

    Route::prefix('setting')->group(function() {
        Route::get('/', [SettingController::class, 'dashboard'])->name('setting');
    });
});
