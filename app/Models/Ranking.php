<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ranking extends Model
{
    protected $fillable = [
        'user_id',
        'map_id',
        'score',
        'max_combo',
        'accuracy',
        'grade'
    ];

    protected $casts = [
        'score' => 'integer',
        'max_combo' => 'integer',
        'accuracy' => 'float'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function map(): BelongsTo
    {
        return $this->belongsTo(Map::class);
    }

    // Calcular el grado basado en la precisión
    public static function calculateGrade(float $accuracy): string
    {
        return match(true) {
            $accuracy >= 95 => 'S',
            $accuracy >= 90 => 'A',
            $accuracy >= 80 => 'B',
            $accuracy >= 70 => 'C',
            $accuracy >= 60 => 'D',
            default => 'F',
        };
    }
}
