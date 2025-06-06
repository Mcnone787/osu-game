<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('game.profile');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function show()
    {
        $user = auth()->user();
        
        // Obtener estadísticas reales del usuario
        $totalGames = \App\Models\Ranking::where('user_id', $user->id)->count();
        
        // Calcular precisión promedio
        $averageAccuracy = \App\Models\Ranking::where('user_id', $user->id)
            ->avg('accuracy');
        
        // Obtener ranking global del usuario (posición)
        $userRank = \App\Models\Ranking::selectRaw('user_id, MAX(score) as max_score')
            ->groupBy('user_id')
            ->orderByDesc('max_score')
            ->get()
            ->search(function($ranking) use ($user) {
                return $ranking->user_id === $user->id;
            });
        $userRank = $userRank !== false ? '#' . ($userRank + 1) : 'N/A';

        // Obtener datos de rendimiento (últimas 10 partidas)
        $performanceData = \App\Models\Ranking::with('map')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function($game) {
                return [
                    'date' => $game->created_at->format('d/m'),
                    'score' => $game->score,
                    'accuracy' => round($game->accuracy, 2),
                    'songName' => $game->map->title
                ];
            })
            ->reverse()
            ->values();

        // Obtener últimas partidas
        $recentGames = \App\Models\Ranking::with('map')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function($game) {
                return [
                    'id' => $game->id,
                    'songName' => $game->map->title,
                    'songImage' => asset('storage/' . $game->map->thumbnail_path),
                    'score' => $game->score,
                    'accuracy' => number_format($game->accuracy, 2),
                    'date' => $game->created_at->format('Y-m-d')
                ];
            });

        return Inertia::render('Game/Profile', [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
            ],
            'stats' => [
                'totalGames' => $totalGames,
                'accuracy' => $averageAccuracy ? number_format($averageAccuracy, 1) . '%' : '0%',
                'rank' => $userRank
            ],
            'recentGames' => $recentGames,
            'performanceData' => $performanceData,
            'status' => session('status')
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'avatar' => ['nullable', 'image', 'max:1024'], // máximo 1MB
        ]);

        // Actualizar avatar si se proporcionó uno nuevo
        if ($request->hasFile('avatar')) {
            // Eliminar avatar anterior si existe
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->name = $request->name;
        if ($user->email !== $request->email) {
            $user->email = $request->email;
            $user->email_verified_at = null;
        }
        
        $user->save();

        return redirect()->route('game.profile')->with('status', '¡Perfil actualizado correctamente!');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        auth()->user()->update([
            'password' => Hash::make($request->password)
        ]);

        return redirect()->route('game.profile')->with('status', '¡Contraseña actualizada correctamente!');
    }

    public function deleteAccount(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = auth()->user();

        Auth::logout();
        
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }
        
        $user->delete();

        return redirect()->route('game.home')->with('status', 'Cuenta eliminada correctamente.');
    }
}
