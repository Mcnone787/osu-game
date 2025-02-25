<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Map;

class GameController extends Controller
{
    public function index()
    {
        return Inertia::render('Game/Home');
    }

    public function play(Request $request)
    {
        if ($request->wantsJson()) {
            $songs = Map::with('user')
                ->orderBy('created_at', 'desc')
                ->paginate(5);

            return response()->json([
                'data' => $songs->through(function ($map) {
                    return $this->transformMap($map);
                }),
                'next_page_url' => $songs->nextPageUrl(),
                'has_more' => $songs->hasMorePages()
            ]);
        }

        // Para la carga inicial de la página
        $songs = Map::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return Inertia::render('Game/Play', [
            'initialSongs' => [
                'data' => $songs->through(function ($map) {
                    return $this->transformMap($map);
                })->values(),
                'next_page_url' => $songs->nextPageUrl(),
                'has_more' => $songs->hasMorePages()
            ],
            'currentUser' => auth()->user() ? [
                'name' => auth()->user()->name,
                'guest' => false
            ] : [
                'name' => 'Guest',
                'guest' => true
            ]
        ]);
    }

    public function startGame($map)
    {
        $maptoFind = Map::find($map);
        return Inertia::render('Game/GamePlay', [
            'map' => [
                'id' => $maptoFind->id,
                'title' => $maptoFind->title,
                'artist' => $maptoFind->artist,
                'difficulty' => strtoupper($maptoFind->difficulty),
                'bpm' => $maptoFind->bpm,
                'audio_path' => asset('storage/' . $maptoFind->audio_path),
                'notes' => json_decode($maptoFind->notes, true) ?? [],
                'creator' => $maptoFind->user->name,
                'image_path' => asset('storage/' . $maptoFind->image_path),
                'thumbnail_path' => asset('storage/' . $maptoFind->thumbnail_path),
                'video_path' => asset('storage/' . $maptoFind->video_path),
                'slug' => $maptoFind->slug
            ]
        ]);
    }

    private function transformMap($map)
    {
        return [
            'id' => $map->id,
            'title' => $map->title,
            'artist' => $map->artist,
            'difficulty' => strtoupper($map->difficulty),
            'bpm' => $map->bpm,
            'thumbnail' => asset('imgs/default-song.jpg'),
            'length' => '00:00',
            'creator' => $map->user->name,
            'audio_path' => asset('storage/' . $map->audio_path),
            'rankings' => $this->getMockRankings(),
            'image_path' => asset('storage/' . $map->image_path),
            'thumbnail_path' => asset('storage/' . $map->thumbnail_path),
            'video_path' => asset('storage/' . $map->video_path),
            'slug' => $map->slug
        ];
    }

    private function getMockRankings()
    {
        return [
            'global' => [
                [
                    'position' => 1,
                    'player' => "★ MasterRhythm ★",
                    'score' => 1234567,
                    'accuracy' => 99.87,
                    'combo' => "1,458",
                    'date' => now()->subDays(1)->format('Y-m-d'),
                    'avatar' => asset('imgs/avatars/default.jpg'),
                    'level' => 99
                ],
                // ... otros rankings
            ],
            'friends' => [
                // ... rankings de amigos
            ],
            'country' => [
                // ... rankings por país
            ]
        ];
    }
}
