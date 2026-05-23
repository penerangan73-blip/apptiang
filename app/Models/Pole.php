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
        'sub_name',
        'customer_id',
        'street_name',
        'address',
        'village',
        'district',
        'regency',
        'province',
        'latitude',
        'longitude',
        'power_usage',
        'condition',
        'pole_type',
        'height',
        'ornament_type',
        'lamp_type',
        'lamp_status',
        'lamp_house_type',
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

    public function displayName(): string
    {
        return collect([$this->customer_id, $this->sub_name])
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->implode('/');
    }

    public function addressLine(): string
    {
        return trim((string) ($this->address ?: $this->street_name));
    }

    public function administrativeAddress(): string
    {
        return collect([$this->village, $this->district, $this->regency, $this->province])
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->implode(', ');
    }

    public function locationLabel(): string
    {
        return collect([$this->addressLine(), $this->administrativeAddress()])
            ->filter()
            ->implode(', ');
    }

    public function scopeSearch(Builder $query, ?string $term, ?array $searchColumns = null): Builder
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

        $defaultColumns = [
            'id',
            'sub_name',
            'customer_id',
            'street_name',
            'address',
            'village',
            'district',
            'regency',
            'province',
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
            'lamp_house_type',
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

        $requestedColumns = $searchColumns === null ? null : collect($searchColumns)
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values();
        $searchCreatedBy = $requestedColumns === null || $requestedColumns->contains('created_by');
        $searchUpdatedBy = $requestedColumns === null || $requestedColumns->contains('updated_by');
        $searchDate = $requestedColumns === null || $requestedColumns->intersect(['created_at', 'updated_at'])->isNotEmpty();
        $columns = $requestedColumns === null
            ? $defaultColumns
            : $requestedColumns->reject(fn ($column) => in_array($column, ['created_by', 'updated_by'], true))->all();
        $columns = $this->existingColumns($this->getTable(), $columns);

        return $query->where(function (Builder $builder) use ($term, $terms, $columns, $searchCreatedBy, $searchUpdatedBy, $searchDate) {
            foreach ($terms as $searchTerm) {
                foreach ($columns as $column) {
                    $builder->orWhere($column, 'like', "%{$searchTerm}%");
                }
            }

            $builder
                ->when($searchDate && preg_match('/^\d{4}-\d{2}-\d{2}$/', $term), function (Builder $builder) use ($term) {
                    $builder->orWhereDate('created_at', $term)->orWhereDate('updated_at', $term);
                })
                ->when($searchDate && preg_match('/^\d{4}$/', $term), function (Builder $builder) use ($term) {
                    $builder->orWhereYear('created_at', (int) $term)->orWhereYear('updated_at', (int) $term);
                })
                ->when($searchCreatedBy, fn (Builder $builder) => $builder->orWhereHas('createdBy', fn (Builder $userQuery) => $this->searchUserColumns($userQuery, $terms)))
                ->when($searchUpdatedBy, fn (Builder $builder) => $builder->orWhereHas('updatedBy', fn (Builder $userQuery) => $this->searchUserColumns($userQuery, $terms)));
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
