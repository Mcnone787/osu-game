<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\MapController; // Nuevo controlador
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [GameController::class, 'index'])->name('game.home');
Route::get('/game/play', [GameController::class, 'play'])->name('game.play');

Route::middleware(['auth'])->group(function () {
    // Rutas para gestión de mapas
    Route::get('/maps', [MapController::class, 'index'])->name('maps.index');
    Route::get('/maps/create', [MapController::class, 'create'])->name('maps.create');
    Route::post('/maps', [MapController::class, 'store'])->name('maps.store');
    Route::get('/maps/{map}', [MapController::class, 'show'])->name('maps.show');
    Route::delete('/maps/{map}', [MapController::class, 'destroy'])->name('maps.destroy');
    
    // Ruta para subir el archivo de audio
    Route::post('/maps/upload-audio', [MapController::class, 'uploadAudio'])->name('maps.upload-audio');
});

require __DIR__.'/auth.php';
