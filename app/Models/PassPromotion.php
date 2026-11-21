<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PassPromotion extends Model
{
      protected $fillable = [
        'user_id',
        'promotion_actuelle',
        'promotion_visée',
        'youtube_url',
        'thumbnail',
        'youtube_playlist_id',
        'position',
    ];
}
