<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MultiplayerController;
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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('multiplayer')->group(function () {

    Route::post('/create', [MultiplayerController::class, 'createRoom']);
    Route::post('/join',   [MultiplayerController::class, 'joinRoom']);

    Route::get('/lobby/{code}', [MultiplayerController::class, 'roomState']);
    Route::post('/pick',        [MultiplayerController::class, 'pickOrder']);

    Route::get('/game-state/{code}', [MultiplayerController::class, 'gameState']);
    Route::post('/answer',            [MultiplayerController::class, 'submitAnswer']);

    Route::post('/sticker', [MultiplayerController::class, 'sendSticker']);
});

