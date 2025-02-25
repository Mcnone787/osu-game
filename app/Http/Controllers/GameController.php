<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Map;
use getID3; // Añadido el import de getID3

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
                ->paginate(10);

            // Debug log
            \Log::info('Pagination info:', [
                'current_page' => $songs->currentPage(),
                'total' => $songs->total(),
                'has_more' => $songs->hasMorePages()
            ]);

            return response()->json([
                'data' => $songs->through(function ($map) {
                    return $this->transformMap($map);
                })->values(),
                'has_more' => $songs->hasMorePages(),
                'current_page' => $songs->currentPage()
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
                'has_more' => $songs->hasMorePages(),
                'current_page' => $songs->currentPage()
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
        // Obtener la duración del archivo de audio
        $duration = 0;
        $audioPath = storage_path('app/public/' . $map->audio_path);
        
        if (file_exists($audioPath)) {
            try {
                $getID3 = new \getID3();
                $fileInfo = $getID3->analyze($audioPath);
                $duration = isset($fileInfo['playtime_seconds']) ? $fileInfo['playtime_seconds'] : 0;
            } catch (\Exception $e) {
                \Log::error('Error al obtener duración del audio: ' . $e->getMessage());
            }
        }

        return [
            'id' => $map->id,
            'title' => $map->title,
            'artist' => $map->artist,
            'difficulty' => strtoupper($map->difficulty),
            'bpm' => $map->bpm,
            'thumbnail' => asset('imgs/default-song.jpg'),
            'length' => $this->formatDuration($duration), // Formato mm:ss
            'duration' => $duration, // Duración en segundos
            'creator' => $map->user->name,
            'audio_path' => asset('storage/' . $map->audio_path),
            'rankings' => $this->getMockRankings(),
            'image_path' => asset('storage/' . $map->image_path),
            'have_image' => $map->image_path,
            'thumbnail_path' => asset('storage/' . $map->thumbnail_path),
            'video_path' => asset('storage/' . $map->video_path),
            'slug' => $map->slug
        ];
    }

    private function formatDuration($seconds)
    {
        $minutes = floor($seconds / 60);
        $remainingSeconds = floor($seconds % 60);
        return sprintf('%d:%02d', $minutes, $remainingSeconds);
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

    private function getAudioDuration($audioPath)
    {
        try {
            $command = "ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 " . escapeshellarg($audioPath);
            $duration = trim(shell_exec($command));
            return $duration ? floatval($duration) : 0;
        } catch (\Exception $e) {
            \Log::error('Error al obtener duración del audio: ' . $e->getMessage());
            return 0;
        }
    }
}
