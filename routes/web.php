<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\MapController; // Nuevo controlador
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Auth\GameAuthController;

Route::get('/', [GameController::class, 'index'])->name('game.home');
Route::get('/game/play', [GameController::class, 'play'])->name('game.play');
Route::get('/game/play/{map}', [GameController::class, 'startGame'])->name('game.play.start');

Route::middleware(['auth'])->group(function () {
    // Rutas para gestión de mapas
    Route::get('/maps', [MapController::class, 'index'])->name('maps.index');
    Route::get('/maps/create', [MapController::class, 'create'])->name('maps.create');
    Route::get('/maps/{map}/edit', [MapController::class, 'edit'])->name('maps.edit');
    Route::post('/maps', [MapController::class, 'store'])->name('maps.store');
    Route::put('/maps/{map}', [MapController::class, 'update'])->name('maps.update');
    Route::get('/maps/{map}', [MapController::class, 'show'])->name('maps.show');
    Route::delete('/maps/{map}', [MapController::class, 'destroy'])->name('maps.destroy');
    
    // Ruta para subir el archivo de audio
    Route::post('/maps/upload-audio', [MapController::class, 'uploadAudio'])->name('maps.upload-audio');

    // Rutas del perfil
    Route::get('/game/profile', [ProfileController::class, 'show'])->name('game.profile');
    Route::post('/game/profile', [ProfileController::class, 'updateProfile'])->name('game.profile.update');
    Route::post('/game/profile/password', [ProfileController::class, 'updatePassword'])->name('game.profile.password');
    Route::delete('/game/profile', [ProfileController::class, 'deleteAccount'])->name('game.profile.delete');
});

Route::post('/game/login', [GameAuthController::class, 'login'])->name('game.login');
Route::post('/game/register', [GameAuthController::class, 'register'])->name('game.register');
Route::post('/game/logout', [GameAuthController::class, 'logout'])->name('game.logout');

// Rutas para rankings
Route::post('/game/rankings/{map}', [GameController::class, 'saveRanking'])->name('game.rankings.save')->middleware('auth');
Route::get('/game/rankings/{map}', [GameController::class, 'getRankings'])->name('game.rankings.get');

require __DIR__.'/auth.php';
