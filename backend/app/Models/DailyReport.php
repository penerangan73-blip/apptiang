<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyReport extends Model
{
    protected $fillable = [
        'user_id',
        'pole_id',
        'report_date',
        'photo_url',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'date:Y-m-d',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pole(): BelongsTo
    {
        return $this->belongsTo(Pole::class);
    }
}
