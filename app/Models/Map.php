<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Map extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'artist',
        'difficulty',
        'bpm',
        'audio_path',
        'notes',
        'slug',
        'image_path',
        'video_path',
        'thumbnail_path'
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