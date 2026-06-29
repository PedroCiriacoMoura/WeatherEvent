<?php

namespace App\Http\Requests\Event;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;
use MatanYadaev\EloquentSpatial\Enums\Srid;
use MatanYadaev\EloquentSpatial\Objects\Point;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Event::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:event_categories,id'],
            'status_id' => ['required', 'integer', 'exists:event_statuses,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'city' => ['required', 'string', 'max:120'],
            'country' => ['required', 'string', 'size:2'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'timezone' => ['nullable', 'timezone'],
            'attendees' => ['nullable', 'integer', 'min:0'],
            'is_outdoor' => ['boolean'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        $validated = $this->validated();

        $attributes = collect($validated)
            ->except(['latitude', 'longitude'])
            ->all();

        $attributes['user_id'] = $this->user()->id;
        $attributes['coordinates'] = new Point(
            (float) $validated['latitude'],
            (float) $validated['longitude'],
            Srid::WGS84,
        );

        return $attributes;
    }
}
