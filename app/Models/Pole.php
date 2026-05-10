<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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

    public function photos(): HasMany
    {
        return $this->hasMany(PolePhoto::class)->orderBy('sort_order')->orderBy('id');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $builder) use ($term) {
            $builder
                ->where('street_name', 'like', "%{$term}%")
                ->orWhere('customer_id', 'like', "%{$term}%")
                ->when(preg_match('/^\d{4}-\d{2}-\d{2}$/', $term), fn (Builder $builder) => $builder->orWhereDate('created_at', $term))
                ->when(preg_match('/^\d{4}$/', $term), fn (Builder $builder) => $builder->orWhereYear('created_at', (int) $term))
                ->orWhereHas('createdBy', function (Builder $userQuery) use ($term) {
                    $userQuery
                        ->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%");
                });
        });
    }

    public function scopeCondition(Builder $query, ?string $condition): Builder
    {
        if (! in_array($condition, ['baik', 'rusak', 'perbaikan'], true)) {
            return $query;
        }

        return $query->where('condition', $condition);
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
