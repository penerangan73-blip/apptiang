<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

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
        'ornament_type',
        'lamp_type',
        'lamp_status',
        'lamp_house_condition',
        'light_point_count',
        'kwh_meter',
        'ownership',
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
            'light_point_count' => 'integer',
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

        $terms = collect([
            $term,
            str_replace(' ', '_', $term),
            str_replace('_', ' ', $term),
        ])->map(fn (string $value) => trim($value))->filter()->unique()->values();

        $columns = [
            'id',
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
            'ornament_type',
            'lamp_type',
            'lamp_status',
            'lamp_house_condition',
            'light_point_count',
            'kwh_meter',
            'ownership',
            'pln_transformer',
            'photo_url',
            'notes',
            'created_by_id',
            'updated_by_id',
            'created_at',
            'updated_at',
        ];

        $columns = $this->existingColumns($this->getTable(), $columns);

        return $query->where(function (Builder $builder) use ($term, $terms, $columns) {
            foreach ($terms as $searchTerm) {
                foreach ($columns as $column) {
                    $builder->orWhere($column, 'like', "%{$searchTerm}%");
                }
            }

            $builder
                ->when(preg_match('/^\d{4}-\d{2}-\d{2}$/', $term), function (Builder $builder) use ($term) {
                    $builder->orWhereDate('created_at', $term)->orWhereDate('updated_at', $term);
                })
                ->when(preg_match('/^\d{4}$/', $term), function (Builder $builder) use ($term) {
                    $builder->orWhereYear('created_at', (int) $term)->orWhereYear('updated_at', (int) $term);
                })
                ->orWhereHas('createdBy', fn (Builder $userQuery) => $this->searchUserColumns($userQuery, $terms))
                ->orWhereHas('updatedBy', fn (Builder $userQuery) => $this->searchUserColumns($userQuery, $terms));
        });
    }

    private function searchUserColumns(Builder $query, $terms): void
    {
        $columns = $this->existingColumns('users', ['name', 'email', 'role', 'phone', 'address']);

        $query->where(function (Builder $builder) use ($terms, $columns) {
            foreach ($terms as $searchTerm) {
                foreach ($columns as $column) {
                    $builder->orWhere($column, 'like', "%{$searchTerm}%");
                }
            }
        });
    }

    private function existingColumns(string $table, array $columns): array
    {
        static $columnCache = [];

        if (! array_key_exists($table, $columnCache)) {
            $columnCache[$table] = Schema::getColumnListing($table);
        }

        return array_values(array_intersect($columns, $columnCache[$table]));
    }

    public function scopeCondition(Builder $query, ?string $condition): Builder
    {
        if (! in_array($condition, ['keropos', 'miring', 'berkarat', 'kokoh'], true)) {
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
