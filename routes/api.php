<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/api-checker', [
    App\Http\Controllers\ApiCheckerController::class,
    'index',
]);
