<?php

namespace App\Models;

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
}
