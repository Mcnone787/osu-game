<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class GameController extends Controller
{
    public function index()
    {
        return Inertia::render('Game/Home');
    }

    public function play()
    {
        // Aquí posteriormente añadiremos la lógica para obtener las canciones
        $songs = [
            [
                'id' => 1,
                'title' => 'Song 1',
                'artist' => 'Artist 1',
                'difficulty' => 'Hard',
                'bpm' => 180
            ],
            // Más canciones...
        ];

        return Inertia::render('Game/Play', [
            'songs' => $songs
        ]);
    }
}
