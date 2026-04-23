<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MahJongTile extends Model
{
    protected $table = 'mah_jong_tiles';

    protected $fillable = [
        'type',
        'number',
        'copy_no',
    ];

    public function matchTiles()
    {
        return $this->hasMany(MahJongMatchTile::class, 'mah_jong_tile_id');
    }
}
