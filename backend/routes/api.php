<?php

use App\Http\Controllers\Api\LinkController;
use App\Http\Controllers\Api\TagController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('links', [LinkController::class, 'index']);
    Route::post('links', [LinkController::class, 'store'])->middleware('throttle:30,1');
    Route::patch('links/{link}', [LinkController::class, 'update']);
    Route::delete('links/{link}', [LinkController::class, 'destroy']);

    Route::get('tags', [TagController::class, 'index']);
});
