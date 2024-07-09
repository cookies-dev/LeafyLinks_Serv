<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\PlantController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the 'api' middleware group. Make something great!
|
*/

/*
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
*/

Route::group(['prefix' => 'users', 'as' => 'user.'], function () {
    Route::get('{id}', [UserController::class, 'getById'])->name('get')->whereNumber('id');
    Route::any('login', [UserController::class, 'login'])->name('login');
    Route::post('register', [UserController::class, 'register'])->name('register');
    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::any('me', [UserController::class, 'me'])->name('me');
        Route::put('me', [UserController::class, 'edit'])->name('edit');
        Route::delete('me', [UserController::class, 'destroy'])->name('destroy');
        Route::post('logout', [UserController::class, 'logout'])->name('logout');
    });
});

Route::group(['prefix' => 'locations', 'as' => 'location.'], function () {
    Route::get('{lat}&{lng}/{dist?}', [LocationController::class, 'getNearestLocations'])->whereNumber(['lat', 'lng', 'dist'])->name('nearest');
    Route::get('{id}', [LocationController::class, 'getLocationById'])->name('getId')->whereNumber('id');
    Route::get('user/{userId}', [LocationController::class, 'getUserLocations'])->name('user')->whereNumber('id');
    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::get('', [LocationController::class, 'getLocations'])->name('get');
        Route::post('', [LocationController::class, 'create'])->name('create');
        Route::put('{id}', [LocationController::class, 'edit'])->name('edit')->whereNumber('id');
        Route::delete('{id}', [LocationController::class, 'delete'])->name('delete')->whereNumber('id');
    });
    Route::get('{idLocation}/plants', [PlantController::class, 'plants'])->name('plants');
});

Route::group(['prefix' => 'plants', 'as' => 'plant.'], function () {
    Route::get('', [PlantController::class, 'all'])->name('all');
    Route::get('{id}', [PlantController::class, 'get'])->name('get')->whereNumber('id');
    Route::get('search/query={query}&limit={limit}', [PlantController::class, 'search'])->name('search');
    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::post('', [PlantController::class, 'create'])->name('create')->middleware('optimizeImages');
        Route::put('{id}', [PlantController::class, 'edit'])->name('edit')->whereNumber('id');
        Route::delete('{id}', [PlantController::class, 'delete'])->name('delete')->whereNumber('id');
    });
    Route::get('plants/{plantId}/comments/', [CommentController::class, 'get'])->name('comments');
});

Route::group(['prefix' => 'comments', 'as' => 'comment.'], function () {
    Route::get('{id}', [CommentController::class, 'getById'])->name('get')->whereNumber('id');
    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::post('', [CommentController::class, 'create'])->name('create');
        Route::put('{id}', [CommentController::class, 'edit'])->name('edit')->whereNumber('id');
        Route::delete('{id}', [CommentController::class, 'delete'])->name('delete')->whereNumber('id');
    });
});
