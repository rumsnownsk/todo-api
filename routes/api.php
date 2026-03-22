<?php

use App\Http\Controllers\api\v1\StatusController;
use App\Http\Controllers\api\v1\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware('api')->prefix('v1')->group(function () {
    Route::apiResource('tasks', TaskController::class)
        ->parameter('tasks', 'id')
        ->except(['create', 'edit']);
    Route::apiResource('statuses', StatusController::class)
        ->only(['index', 'show']);


    Route::get('user', function (Request $request) {
        return $request->user();
    })->middleware('auth:sanctum');
});
