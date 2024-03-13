<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Resources\UserResource;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\PostController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) { 
        return new UserResource($request->user());
    });

    Route::put('/categories/reorder', [CategoryController::class, 'reorder'])->name('categories.reorder');

    Route::apiResources([
        'categories' => CategoryController::class,
        'posts' => PostController::class,
    ]);
});




