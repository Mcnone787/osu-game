<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Map;
use getID3; // Añadido el import de getID3
use App\Models\Ranking;

class GameController extends Controller
{
    public function index()
    {
        return Inertia::render('Game/Home');
    }

    public function play(Request $request)
    {
        if ($request->wantsJson()) {
            try {
                // Aumentar temporalmente el límite de tiempo de ejecución
                set_time_limit(120);
                
                $songs = Map::with('user')
                    ->select('id', 'title', 'artist', 'difficulty', 'bpm', 'audio_path', 'image_path', 'thumbnail_path', 'video_path', 'slug', 'user_id')
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
            } catch (\Exception $e) {
                \Log::error('Error cargando canciones: ' . $e->getMessage());
                return response()->json([
                    'error' => 'Error al cargar las canciones',
                    'message' => $e->getMessage()
                ], 500);
            }
        }

        // Para la carga inicial de la página
        try {
            // Aumentar temporalmente el límite de tiempo de ejecución
            set_time_limit(120);
            
            $songs = Map::with('user')
                ->select('id', 'title', 'artist', 'difficulty', 'bpm', 'audio_path', 'image_path', 'thumbnail_path', 'video_path', 'slug', 'user_id')
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
        } catch (\Exception $e) {
            \Log::error('Error en la carga inicial: ' . $e->getMessage());
            return Inertia::render('Game/Play', [
                'initialSongs' => [
                    'data' => [],
                    'has_more' => false,
                    'current_page' => 1
                ],
                'currentUser' => auth()->user() ? [
                    'name' => auth()->user()->name,
                    'guest' => false
                ] : [
                    'name' => 'Guest',
                    'guest' => true
                ],
                'error' => 'Error al cargar las canciones'
            ]);
        }
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
        try {
            // Cache key para la duración del audio
            $cacheKey = 'audio_duration_' . $map->id;
            
            // Intentar obtener la duración del cache
            $duration = \Cache::remember($cacheKey, 3600, function () use ($map) {
                $audioPath = storage_path('app/public/' . $map->audio_path);
                
                if (file_exists($audioPath)) {
                    try {
                        $getID3 = new \getID3();
                        $fileInfo = $getID3->analyze($audioPath);
                        return isset($fileInfo['playtime_seconds']) ? $fileInfo['playtime_seconds'] : 0;
                    } catch (\Exception $e) {
                        \Log::error('Error al obtener duración del audio: ' . $e->getMessage());
                        return 0;
                    }
                }
                return 0;
            });

            return [
                'id' => $map->id,
                'title' => $map->title,
                'artist' => $map->artist,
                'difficulty' => strtoupper($map->difficulty),
                'bpm' => $map->bpm,
                'thumbnail' => asset('imgs/default-song.jpg'),
                'length' => $this->formatDuration($duration),
                'duration' => $duration,
                'creator' => $map->user->name,
                'audio_path' => asset('storage/' . $map->audio_path),
                'rankings' => $this->getMockRankings(),
                'image_path' => asset('storage/' . $map->image_path),
                'have_image' => !empty($map->image_path),
                'thumbnail_path' => asset('storage/' . $map->thumbnail_path),
                'video_path' => asset('storage/' . $map->video_path),
                'slug' => $map->slug
            ];
        } catch (\Exception $e) {
            \Log::error('Error transformando mapa: ' . $e->getMessage());
            return null;
        }
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

    public function saveRanking(Request $request, Map $map)
    {
        try {
            $request->validate([
                'score' => 'required|integer|min:0',
                'max_combo' => 'required|integer|min:0',
                'accuracy' => 'required|numeric|min:0|max:100'
            ]);

            // Calcular el grado
            $grade = Ranking::calculateGrade($request->accuracy);

            // Buscar si ya existe un ranking para este usuario y mapa
            $existingRanking = Ranking::where('user_id', auth()->id())
                ->where('map_id', $map->id)
                ->first();

            if ($existingRanking) {
                // Si existe, actualizar solo si la nueva puntuación es mejor
                if ($request->score > $existingRanking->score) {
                    $existingRanking->update([
                        'score' => $request->score,
                        'max_combo' => $request->max_combo,
                        'accuracy' => $request->accuracy,
                        'grade' => $grade
                    ]);

                    return response()->json([
                        'success' => true,
                        'ranking' => $existingRanking,
                        'message' => '¡Nueva mejor puntuación!'
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'ranking' => $existingRanking,
                    'message' => 'Puntuación registrada (no superó tu mejor marca)'
                ]);
            }

            // Si no existe, crear nuevo ranking
            $ranking = Ranking::create([
                'user_id' => auth()->id(),
                'map_id' => $map->id,
                'score' => $request->score,
                'max_combo' => $request->max_combo,
                'accuracy' => $request->accuracy,
                'grade' => $grade
            ]);

            return response()->json([
                'success' => true,
                'ranking' => $ranking,
                'message' => '¡Primera puntuación registrada!'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error guardando ranking: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la puntuación'
            ], 500);
        }
    }

    public function getRankings(Map $map)
    {
        try {
            $rankings = [
                'global' => Ranking::with('user')
                    ->where('map_id', $map->id)
                    ->orderByDesc('score')
                    ->take(10)
                    ->get()
                    ->map(function ($ranking) {
                        return [
                            'position' => $ranking->position,
                            'player' => $ranking->user->name,
                            'score' => $ranking->score,
                            'accuracy' => $ranking->accuracy,
                            'combo' => number_format($ranking->max_combo),
                            'date' => $ranking->created_at->format('Y-m-d'),
                            'avatar' => asset('imgs/avatars/default.jpg'),
                            'level' => 1, // Implementar sistema de niveles después
                            'grade' => $ranking->grade
                        ];
                    }),

                'friends' => auth()->check() ? 
                    Ranking::with('user')
                        ->whereHas('user', function ($query) {
                            // Aquí implementarías la lógica para obtener amigos
                            $query->where('id', '!=', auth()->id());
                        })
                        ->where('map_id', $map->id)
                        ->orderByDesc('score')
                        ->take(10)
                        ->get()
                        ->map(function ($ranking) {
                            return [
                                'position' => $ranking->position,
                                'player' => $ranking->user->name,
                                'score' => $ranking->score,
                                'accuracy' => $ranking->accuracy,
                                'combo' => number_format($ranking->max_combo),
                                'date' => $ranking->created_at->format('Y-m-d'),
                                'avatar' => asset('imgs/avatars/default.jpg'),
                                'level' => 1,
                                'grade' => $ranking->grade
                            ];
                        }) : [],

                'country' => Ranking::with('user')
                    ->where('map_id', $map->id)
                    // Aquí implementarías la lógica para filtrar por país
                    ->orderByDesc('score')
                    ->take(10)
                    ->get()
                    ->map(function ($ranking) {
                        return [
                            'position' => $ranking->position,
                            'player' => $ranking->user->name,
                            'score' => $ranking->score,
                            'accuracy' => $ranking->accuracy,
                            'combo' => number_format($ranking->max_combo),
                            'date' => $ranking->created_at->format('Y-m-d'),
                            'avatar' => asset('imgs/avatars/default.jpg'),
                            'level' => 1,
                            'grade' => $ranking->grade
                        ];
                    })
            ];

            return response()->json($rankings);

        } catch (\Exception $e) {
            \Log::error('Error obteniendo rankings: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al obtener los rankings'
            ], 500);
        }
    }
}
