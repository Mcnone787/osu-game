<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Map;
use App\Models\User;

class MapSeeder extends Seeder
{
    public function run()
    {
        // Asegurarnos de que hay al menos un usuario
        $user = User::first() ?? User::factory()->create();

        // Lista de canciones de ejemplo
        $songs = [
            ['title' => 'Gurenge', 'artist' => 'LiSA'],
            ['title' => 'Unravel', 'artist' => 'TK from Ling Tosite Sigure'],
            ['title' => 'The Rumbling', 'artist' => 'SiM'],
            ['title' => 'Cruel Angel\'s Thesis', 'artist' => 'Yoko Takahashi'],
            ['title' => 'Blue Bird', 'artist' => 'Ikimono Gakari'],
            ['title' => 'Again', 'artist' => 'Yui'],
            ['title' => 'Silhouette', 'artist' => 'KANA-BOON'],
            ['title' => 'Crossing Field', 'artist' => 'LiSA'],
            ['title' => 'Colors', 'artist' => 'FLOW'],
            ['title' => 'Black Catcher', 'artist' => 'Vickeblanka'],
            ['title' => 'Kaikai Kitan', 'artist' => 'Eve'],
            ['title' => 'Departure!', 'artist' => 'Masatoshi Ono'],
            ['title' => 'Inferno', 'artist' => 'Mrs. GREEN APPLE'],
            ['title' => 'Odd Future', 'artist' => 'UVERworld'],
            ['title' => 'Peace Sign', 'artist' => 'Kenshi Yonezu']
        ];

        // Dificultades disponibles
        $difficulties = ['easy', 'normal', 'hard', 'expert'];

        // Crear mapas de ejemplo
        foreach ($songs as $song) {
            Map::create([
                'user_id' => $user->id,
                'title' => $song['title'],
                'artist' => $song['artist'],
                'bpm' => rand(120, 180),
                'difficulty' => $difficulties[array_rand($difficulties)],
                'audio_path' => 'maps/audio/example.mp3', // Ruta de ejemplo
                'notes' => json_encode([
                    ['id' => 1, 'lane' => 0, 'time' => 1.0],
                    ['id' => 2, 'lane' => 1, 'time' => 1.5],
                    ['id' => 3, 'lane' => 2, 'time' => 2.0],
                    ['id' => 4, 'lane' => 3, 'time' => 2.5],
                ]),
                'slug' => \Str::slug($song['title'] . '-' . \Str::random(8))
            ]);
        }
    }
} 