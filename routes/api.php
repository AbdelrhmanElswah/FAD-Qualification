<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\TaskController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::apiResource('tasks', TaskController::class)
        ->parameters(['tasks' => 'id'])
        ->where(['id' => '[0-9]+']);
});
