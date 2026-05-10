<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PolePhoto extends Model
{
    protected $fillable = [
        'pole_id',
        'photo_url',
        'sort_order',
    ];

    public function pole(): BelongsTo
    {
        return $this->belongsTo(Pole::class);
    }
}
