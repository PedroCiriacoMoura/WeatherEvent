<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;

class Event extends Model
{
    use HasFactory;
    use HasSpatial;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'status_id',
        'name',
        'description',
        'city',
        'country',
        'coordinates',
        'starts_at',
        'ends_at',
        'timezone',
        'attendees',
        'is_outdoor',
    ];

    protected $casts = [
        'coordinates' => Point::class,
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'attendees' => 'integer',
        'is_outdoor' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(EventCategory::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(EventStatus::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['category_id'] ?? null, fn (Builder $q, $value) => $q->where('category_id', $value))
            ->when($filters['status_id'] ?? null, fn (Builder $q, $value) => $q->where('status_id', $value))
            ->when($filters['city'] ?? null, fn (Builder $q, $value) => $q->where('city', $value))
            ->when($filters['country'] ?? null, fn (Builder $q, $value) => $q->where('country', $value))
            ->when(array_key_exists('is_outdoor', $filters), fn (Builder $q) => $q->where('is_outdoor', filter_var($filters['is_outdoor'], FILTER_VALIDATE_BOOLEAN)))
            ->when($filters['starts_after'] ?? null, fn (Builder $q, $value) => $q->where('starts_at', '>=', $value))
            ->when($filters['starts_before'] ?? null, fn (Builder $q, $value) => $q->where('starts_at', '<=', $value))
            ->when($filters['search'] ?? null, fn (Builder $q, $value) => $q->where('name', 'like', '%'.$value.'%'));
    }

    public function scopeSort(Builder $query, string $field, string $direction): Builder
    {
        return $query->orderBy($field, $direction === 'desc' ? 'desc' : 'asc');
    }
}
