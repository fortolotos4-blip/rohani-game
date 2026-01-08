<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\GuessController;
use App\Http\Controllers\SurpriseController;
use App\Http\Controllers\PuzzleController;
use App\Http\Controllers\TtsController;
use App\Http\Controllers\TtsRoomController;
use App\Http\Controllers\MultiplayerController;
/*
|--------------------------------------------------------------------------
| GENERAL
|--------------------------------------------------------------------------
*/
Route::get('/', fn() => view('welcome'));
Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');

/*
|--------------------------------------------------------------------------
| QUIZ
|--------------------------------------------------------------------------
*/
Route::get('/quiz', [QuizController::class, 'index'])->name('quiz.index');
Route::post('/quiz/answer', [QuizController::class, 'answer'])->name('quiz.answer');

/*
|--------------------------------------------------------------------------
| TEBAK GAMBAR
|--------------------------------------------------------------------------
*/
Route::get('/guess/menu', [GuessController::class,'menu'])->name('guess.menu');
Route::get('/guess/single', [GuessController::class,'single'])->name('guess.single');
Route::post('/guess/single/answer', [GuessController::class,'singleAnswer'])->name('guess.single.answer');
Route::get('/guess/duo', [GuessController::class,'duo'])->name('guess.duo');
Route::post('/guess/duo/answer', [GuessController::class,'duoAnswer'])->name('guess.duo.answer');

/*
|--------------------------------------------------------------------------
| SURPRISE
|--------------------------------------------------------------------------
*/
Route::get('/surprise', [SurpriseController::class, 'index'])->name('surprise.index');
Route::post('/surprise/record', [SurpriseController::class, 'record'])->name('surprise.record');

/*
|--------------------------------------------------------------------------
| PUZZLE
|--------------------------------------------------------------------------
*/
Route::get('/puzzle', [PuzzleController::class, 'index'])->name('puzzle.index');

/*
|--------------------------------------------------------------------------
| TTS SINGLEPLAYER
|--------------------------------------------------------------------------
*/
Route::get('/tts', [TtsController::class, 'index'])->name('tts.index');
Route::get('/tts/menu', fn() => view('tts.menu'))->name('tts.menu');

/*
|--------------------------------------------------------------------------
| TTS MULTIPLAYER LOBBY
|--------------------------------------------------------------------------
*/
Route::get('/tts/multiplayer', fn() => view('tts.multiplayer-lobby'))
    ->name('tts.multiplayer.lobby');

/*
|--------------------------------------------------------------------------
| TTS MULTIPLAYER ROOM
|--------------------------------------------------------------------------
*/
Route::prefix('tts/room')->group(function () {

    Route::post('/create', [TtsRoomController::class,'create']);
    Route::post('/join', [TtsRoomController::class,'join']);
    Route::get('/{code}', [TtsRoomController::class,'play'])
    ->name('tts.room.play');


    // RPS
    Route::post('/{code}/rps', [TtsRoomController::class,'rpsSubmit']);

    // REALTIME
    Route::get('/{code}/state', [TtsRoomController::class,'state']);
    Route::get('/{code}/cells', [TtsRoomController::class,'cells']);

    // INPUT
    Route::post('/{code}/cell', [TtsRoomController::class,'updateCell']);

    // VALIDASI KATA
    Route::post('/{code}/check-word', [TtsRoomController::class,'checkWord']);

    // PUZZLE
    Route::get('/{code}/puzzle', [TtsRoomController::class,'puzzle']);
});

Route::prefix('multiplayer')->group(function () {

    // UI
    Route::get('/menu', fn () => view('multiplayer.menu'))->name('multiplayer.menu');
    Route::get('/lobby/{roomCode}', fn ($roomCode) =>
        view('multiplayer.lobby', compact('roomCode'))
    );
    Route::get('/game/{roomCode}', fn ($roomCode) =>
        view('multiplayer.game', compact('roomCode'))
    );

    // LOBBY POLLING (INI YANG HILANG)
    Route::get('/lobby-state/{code}', [MultiplayerController::class, 'lobbyState']);

    // GAME
    Route::get('/game-state/{code}', [MultiplayerController::class, 'gameState']);
    Route::post('/create', [MultiplayerController::class, 'createRoom']);
    Route::post('/join', [MultiplayerController::class, 'joinRoom']);
    Route::post('/answer', [MultiplayerController::class, 'submitAnswer']);
    Route::post('/sticker', [MultiplayerController::class, 'sendSticker']);
});



