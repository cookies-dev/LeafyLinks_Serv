<?php

use App\Http\Controllers\LocationController;
use App\Http\Controllers\UserController;
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

/*
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
*/

Route::group(['prefix' => 'users', 'as' => 'user.'], function () {
    Route::post('login', [UserController::class, 'login'])->name('login');
    Route::post('register', [UserController::class, 'register'])->name('register');
    Route::group(['middleware' => 'auth:sanctum', 'prefix' => 'user'], function () {
        Route::post('me', [UserController::class, 'me'])->name('me');
        Route::put('me', [UserController::class, 'update'])->name('update');
        Route::delete('me', [UserController::class, 'destroy'])->name('destroy');
        Route::post('logout', [UserController::class, 'logout'])->name('logout');
    });
});

Route::group(['prefix' => 'locations', 'as' => 'location.'], function () {
    Route::get('locations/{x}', [LocationController::class, 'getNearestLocations'])->name('nearest');
    Route::get('locations/id/{id}', [LocationController::class, 'getLocationById'])->name('locationById');
    Route::get('locations/user/{userId}', [LocationController::class, 'getUserLocations'])->name('userLocations');
    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::post('locations', [LocationController::class, 'create'])->name('create');
        Route::put('locations/{id}', [LocationController::class, 'edit'])->name('edit');
        Route::delete('locations/{id}', [LocationController::class, 'delete'])->name('delete');
    });
});
