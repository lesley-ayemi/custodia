<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BlockController;
use App\Http\Controllers\Api\HousingAssignmentController;
use App\Http\Controllers\Api\PrisonerController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::apiResource('prisoners', PrisonerController::class)->except(['destroy']);
    Route::post('/prisoners/{prisoner}/archive', [PrisonerController::class, 'archive']);
    Route::get('/prisoners/{prisoner}/housing-history', [HousingAssignmentController::class, 'history']);

    Route::get('/blocks', [BlockController::class, 'index']);
    Route::post('/housing-assignments', [HousingAssignmentController::class, 'store']);
});
