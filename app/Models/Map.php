<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Map extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'artist',
        'difficulty',
        'bpm',
        'audio_path',
        'notes',
        'user_id',
        'slug'
    ];

    protected $casts = [
        'notes' => 'array',
        'bpm' => 'integer'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 