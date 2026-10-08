<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tutorial extends Model
{
    protected $fillable = [
        'title',
        'description',
        'youtube_video_id',
        'youtube_url',
        'thumbnail',
        'youtube_playlist_id',
        'position',
    ];
}
