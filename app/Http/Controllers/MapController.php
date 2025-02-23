<?php

namespace App\Http\Controllers;

use App\Models\Map;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MapController extends Controller
{
    public function index()
    {
        $maps = Map::with('user')
            ->latest()
            ->paginate(10);

        return Inertia::render('Game/Maps/Index', [
            'maps' => $maps
        ]);
    }

    public function create()
    {
        return Inertia::render('Game/Maps/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'artist' => 'required|string|max:255',
            'bpm' => 'required|integer|min:1',
            'difficulty' => 'required|in:easy,normal,hard,expert',
            'audio' => 'required|file|mimes:mpga,mp3,wav,ogg|max:10240',
            'notes' => 'required|json'
        ]);

        try {
            // Debugging
            \Log::info('Iniciando subida de archivo');
            \Log::info('Tipo de archivo: ' . $request->file('audio')->getMimeType());
            \Log::info('Tamaño de archivo: ' . $request->file('audio')->getSize());

            // Verificar que el archivo existe y es válido
            if (!$request->hasFile('audio') || !$request->file('audio')->isValid()) {
                throw new \Exception('El archivo de audio no es válido');
            }

            // Generar slug único
            $slug = Str::slug($request->title . '-' . Str::random(8));

            // Intentar guardar el archivo
            $audioPath = null;
            if ($request->file('audio')) {
                $file = $request->file('audio');
                $fileName = Str::random(40) . '.' . $file->getClientOriginalExtension();
                $audioPath = $file->storeAs('maps/audio', $fileName, 'public');
                
                if (!$audioPath) {
                    throw new \Exception('Error al guardar el archivo de audio');
                }
                
                \Log::info('Archivo guardado en: ' . $audioPath);
            }

            // Crear el mapa
            $map = Map::create([
                'user_id' => auth()->id(),
                'title' => $request->title,
                'artist' => $request->artist,
                'bpm' => $request->bpm,
                'difficulty' => $request->difficulty,
                'audio_path' => $audioPath,
                'notes' => $request->notes,
                'slug' => $slug
            ]);

            return response()->json([
                'success' => true,
                'message' => '¡Mapa creado con éxito!',
                'map' => $map
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al guardar mapa: ' . $e->getMessage());
            
            // Si algo falla, eliminar el archivo de audio si se subió
            if (isset($audioPath)) {
                Storage::disk('public')->delete($audioPath);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el mapa: ' . $e->getMessage(),
                'error_details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Map $map)
    {
        $request->validate([
            'title' => 'string|max:255',
            'artist' => 'string|max:255',
            'bpm' => 'integer|min:1',
            'difficulty' => 'string|in:easy,normal,hard,expert',
            'audio' => 'nullable|file|mimes:mp3,wav,ogg|max:10240',
            'notes' => 'array'
        ]);

        try {
            $data = $request->only(['title', 'artist', 'bpm', 'difficulty', 'notes']);

            // Si hay un nuevo archivo de audio
            if ($request->hasFile('audio')) {
                // Eliminar el audio anterior
                Storage::disk('public')->delete($map->audio_path);
                // Guardar el nuevo audio
                $data['audio_path'] = $request->file('audio')->store('maps/audio', 'public');
            }

            // Actualizar el mapa
            $map->update($data);

            return response()->json([
                'success' => true,
                'message' => '¡Mapa actualizado con éxito!',
                'map' => $map
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el mapa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Map $map)
    {
        return response()->json([
            'map' => $map,
            'audio_url' => Storage::url($map->audio_path)
        ]);
    }

    public function uploadAudio(Request $request)
    {
        $request->validate([
            'audio' => 'required|file|mimes:mp3,wav|max:10240' // max 10MB
        ]);

        $path = $request->file('audio')->store('maps/audio', 'public');

        return response()->json([
            'path' => $path,
            'url' => Storage::url($path)
        ]);
    }

    public function destroy(Map $map)
    {
        try {
            // Eliminar el archivo de audio
            Storage::disk('public')->delete($map->audio_path);
            
            // Eliminar el mapa
            $map->delete();

            return response()->json([
                'success' => true,
                'message' => '¡Mapa eliminado con éxito!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el mapa',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 