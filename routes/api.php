<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OauthController;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\SignatureMiddleware;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/auth/access_token', [OauthController::class, 'generateToken']);

Route::group(['prefix' => 'user', 'middleware' => 'sign-request'], function () {
    Route::post('/register', [AuthController::class, 'register']);

    Route::post('/login', [AuthController::class, 'login']);

})->middleware([SignatureMiddleware::class]);
