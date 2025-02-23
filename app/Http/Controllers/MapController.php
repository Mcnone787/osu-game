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
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'artist' => 'required|string|max:255',
            'difficulty' => 'required|string|in:EASY,NORMAL,HARD,INSANE',
            'bpm' => 'required|integer|min:1',
            'audio_path' => 'required|string',
            'notes' => 'required|array',
        ]);

        $map = Map::create([
            ...$validated,
            'user_id' => auth()->id(),
            'slug' => Str::slug($validated['title']) . '-' . Str::random(6)
        ]);

        return redirect()->route('maps.show', $map)
            ->with('success', '¡Mapa creado exitosamente!');
    }

    public function show(Map $map)
    {
        return Inertia::render('Game/Maps/Show', [
            'map' => $map->load('user')
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
        // Verificar si el usuario es dueño del mapa o admin
        if ($map->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        // Eliminar archivo de audio
        if ($map->audio_path) {
            Storage::delete($map->audio_path);
        }

        $map->delete();

        return redirect()->route('maps.index')
            ->with('success', 'Mapa eliminado exitosamente');
    }
} 