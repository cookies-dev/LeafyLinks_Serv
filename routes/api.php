<?php

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
    Route::get('locations/{lat}&{lng}/{dist?}', [LocationController::class, 'getNearestLocations'])->whereNumber(['lat', 'lng', 'dist'])->name('nearest');
    Route::get('locations/id/{id}', [LocationController::class, 'getLocationById'])->name('getId');
    Route::get('locations/user/{userId?}', [LocationController::class, 'getUserLocations'])->name('user');
    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::post('locations', [LocationController::class, 'create'])->name('create');
        Route::put('locations/{id}', [LocationController::class, 'edit'])->name('edit');
        Route::delete('locations/{id}', [LocationController::class, 'delete'])->name('delete');
    });
    Route::get('locations/{idLocation}/plants', [PlantController::class, 'plants'])->name('plants');
});

Route::group(['prefix' => 'plants', 'as' => 'plant.'], function () {
    Route::get('plant/{id}', [PlantController::class, 'get'])->name('get');
    Route::get('plant/search/query={query}&limit={limit}', [PlantController::class, 'search'])->name('search');
    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::post('plant', [PlantController::class, 'create'])->name('create');
        Route::put('plant/{id}', [PlantController::class, 'edit'])->name('edit');
        Route::delete('plant/{id}', [PlantController::class, 'delete'])->name('delete');
    });
});
