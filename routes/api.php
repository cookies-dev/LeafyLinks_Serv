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

Route::post('login', [UserController::class, 'login'])->name('login');
Route::post('register', [UserController::class, 'register'])->name('register');
Route::group(['middleware' => 'auth:api'], function () {
    Route::post('me', [UserController::class, 'me'])->name('me');
    Route::put('me', [UserController::class, 'update'])->name('update');
    Route::delete('me', [UserController::class, 'destroy'])->name('destroy');
    Route::post('logout', [UserController::class, 'logout'])->name('logout');
});


Route::get('locations/{x}', [LocationController::class, 'getNearestLocations']);
Route::get('locations/id/{id}', [LocationController::class, 'getLocationById']);
Route::get('locations/user/{userId}', [LocationController::class, 'getUserLocations']);
Route::group(['middleware' => 'auth:api'], function () {
    Route::post('locations', [LocationController::class, 'create']);
    Route::put('locations/{id}', [LocationController::class, 'edit']);
    Route::delete('locations/{id}', [LocationController::class, 'delete']);
});
