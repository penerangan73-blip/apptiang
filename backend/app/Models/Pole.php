<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pole extends Model
{
    protected $fillable = [
        'customer_id',
        'street_name',
        'latitude',
        'longitude',
        'power_usage',
        'condition',
        'pole_type',
        'height',
        'span_length',
        'foundation_type',
        'ownership',
        'pln_transformer',
        'photo_url',
        'notes',
        'created_by_id',
        'updated_by_id',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'power_usage' => 'integer',
            'height' => 'float',
            'span_length' => 'float',
        ];
    }

    public function reports(): HasMany
    {
        return $this->hasMany(DailyReport::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }
}
