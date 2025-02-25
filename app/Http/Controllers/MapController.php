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
        if (request()->wantsJson()) {
            // Para peticiones AJAX (scroll infinito)
            $maps = Map::with('user')
                ->orderBy('created_at', 'desc')
                ->paginate(5);

            return response()->json([
                'data' => $maps->items(),
                'next_page_url' => $maps->nextPageUrl(),
                'has_more' => $maps->hasMorePages()
            ]);
        }

        // Para la carga inicial de la página
        return Inertia::render('Game/Maps/Index', [
            'initialMaps' => Map::with('user')
                ->orderBy('created_at', 'desc')
                ->paginate(5)
                ->items()
        ]);
    }

    public function create()
    {
        return Inertia::render('Game/Maps/Create', [
            'isEditing' => false,
            'map' => null
        ]);
    }

    public function store(Request $request)
    {
        try {
            // Validación básica sin el archivo

            $request->validate([
                'title' => 'required|string|max:255',
                'artist' => 'required|string|max:255',
                'bpm' => 'required|integer|min:1',
                'difficulty' => 'required|in:easy,normal,hard,expert',
                'notes' => 'required|json',
                'image' => 'nullable|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'video' => 'nullable|mimes:mp4,mov,avi,wmv,flv,webm|max:20480'
            ]);

            // Validación manual del archivo
            if (!$request->hasFile('audio')) {
                throw new \Exception('No se ha proporcionado ningún archivo de audio');
            }

            $file = $request->file('audio');
            $image = $request->file('image');
            $video = $request->file('video');
            $this->validateFilesExtension($file, ['mp3', 'wav', 'ogg']);
            $this->validateFilesExtension($image, ['jpeg', 'png', 'jpg', 'gif', 'svg']);
            $this->validateFilesExtension($video, ['mp4', 'mov', 'avi', 'wmv', 'flv', 'webm']);


            // Verificar que el archivo es válido
            if (!$file->isValid()) {
                throw new \Exception('El archivo está corrupto o es inválido');
            }

           
            $extension = $file->getClientOriginalExtension();
            $imageExtension = $image->getClientOriginalExtension();
            $videoExtension = $video->getClientOriginalExtension();
            // Generar nombre único para el archivo
            $fileName = time() . '_' . \Str::random(10) . '.' . $extension;
            $imageName = time() . '_' . \Str::random(10) . '.' . $imageExtension;
            $videoName = time() . '_' . \Str::random(10) . '.' . $videoExtension;
            // Intentar guardar el archivo
            $audioPath = $file->storeAs('maps/audio', $fileName, 'public');
            $imagePath = $image->storeAs('maps/image', $imageName, 'public');
            $videoPath = $video->storeAs('maps/video', $videoName, 'public');
            $thumbnailPath = $image->storeAs('maps/thumbnails', $imageName, 'public');
            if (!$audioPath) {
                throw new \Exception('Error al guardar el archivo de audio');
            }

            // Generar slug único
            $slug = \Str::slug($request->title . '-' . \Str::random(8));

            // Procesar imagen si existe
         
            // Crear el mapa
            $map = Map::create([
                'user_id' => auth()->id(),
                'title' => $request->title,
                'artist' => $request->artist,
                'bpm' => $request->bpm,
                'difficulty' => $request->difficulty,
                'audio_path' => $audioPath,
                'notes' => $request->notes,
                'slug' => $slug,
                'image_path' => $imagePath,
                'thumbnail_path' => $thumbnailPath,
                'video_path' => $videoPath ?? null
            ]);

            return response()->json([
                'success' => true,
                'message' => '¡Mapa creado con éxito!',
                'map' => $map
            ]);

        } catch (\Exception $e) {
            // Limpiar archivos si algo falla
            if (isset($audioPath) && Storage::disk('public')->exists($audioPath)) {
                Storage::disk('public')->delete($audioPath);
            }
            if (isset($imagePath) && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            if (isset($videoPath) && Storage::disk('public')->exists($videoPath)) {
                Storage::disk('public')->delete($videoPath);
            }
            if (isset($thumbnailPath) && Storage::disk('public')->exists($thumbnailPath)) {
                Storage::disk('public')->delete($thumbnailPath);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el mapa: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit(Map $map)
    {
        // Verificar que el usuario es el propietario del mapa
       

        return Inertia::render('Game/Maps/Create', [
            'isEditing' => true,
            'map' => $map
        ]);
    }

    public function update(Request $request, Map $map)
    {
        // Verificar que el usuario es el propietario del mapa
       

        try {
            // Validación
            $request->validate([
                'title' => 'required|string|max:255',
                'artist' => 'required|string|max:255',
                'bpm' => 'required|integer|min:1',
                'difficulty' => 'required|in:easy,normal,hard,expert',
                'notes' => 'required|json',
                'audio' => 'nullable|file|mimes:mp3,wav,ogg|max:10240',
                'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'video' => 'nullable|file|mimes:mp4,mov,avi,wmv,flv,webm|max:20480'
            ]);

            // Preparar datos para actualizar
            $data = $request->only(['title', 'artist', 'bpm', 'difficulty', 'notes']);

            // Manejar archivo de audio si se proporciona uno nuevo
            if ($request->hasFile('audio')) {
                // Eliminar archivo anterior
                if ($map->audio_path && Storage::disk('public')->exists($map->audio_path)) {
                    Storage::disk('public')->delete($map->audio_path);
                }
                $data['audio_path'] = $request->file('audio')->store('maps/audio', 'public');
            }

            // Manejar imagen si se proporciona una nueva
            if ($request->hasFile('image')) {
                // Eliminar archivos anteriores
                if ($map->image_path && Storage::disk('public')->exists($map->image_path)) {
                    Storage::disk('public')->delete($map->image_path);
                }
                if ($map->thumbnail_path && Storage::disk('public')->exists($map->thumbnail_path)) {
                    Storage::disk('public')->delete($map->thumbnail_path);
                }
                
                $imagePath = $request->file('image')->store('maps/image', 'public');
                $data['image_path'] = $imagePath;
                $data['thumbnail_path'] = $imagePath; // Podrías procesar un thumbnail aquí si lo necesitas
            }

            // Manejar video si se proporciona uno nuevo
            if ($request->hasFile('video')) {
                // Eliminar archivo anterior
                if ($map->video_path && Storage::disk('public')->exists($map->video_path)) {
                    Storage::disk('public')->delete($map->video_path);
                }
                $data['video_path'] = $request->file('video')->store('maps/video', 'public');
            }

            // Actualizar el mapa
            $map->update($data);

            return response()->json([
                'success' => true,
                'message' => '¡Mapa actualizado con éxito!',
                'map' => $map
            ]);

        } catch (\Exception $e) {
            // Limpiar archivos nuevos si algo falla
            if (isset($data['audio_path']) && Storage::disk('public')->exists($data['audio_path'])) {
                Storage::disk('public')->delete($data['audio_path']);
            }
            if (isset($data['image_path']) && Storage::disk('public')->exists($data['image_path'])) {
                Storage::disk('public')->delete($data['image_path']);
            }
            if (isset($data['video_path']) && Storage::disk('public')->exists($data['video_path'])) {
                Storage::disk('public')->delete($data['video_path']);
            }

            \Log::error('Error actualizando mapa: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el mapa: ' . $e->getMessage()
            ], 500);
        }
    }

    public function validateFilesExtension($file, $allowedExtensions)
    {
        $extension = strtolower(pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions)) {
            throw new \Exception('El tipo de archivo no es válido. Se permiten: ' . implode(', ', $allowedExtensions));
        }
         // Log de información del archivo
         \Log::info('Información del archivo:', [
            'nombre_original' => $file->getClientOriginalName(),
            'extension' => $extension,
            'mime_type' => $file->getMimeType(),
            'tamaño' => $file->getSize()
        ]);
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