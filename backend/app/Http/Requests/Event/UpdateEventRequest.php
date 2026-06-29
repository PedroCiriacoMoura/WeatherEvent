<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;
use MatanYadaev\EloquentSpatial\Enums\Srid;
use MatanYadaev\EloquentSpatial\Objects\Point;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('event'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['sometimes', 'integer', 'exists:event_categories,id'],
            'status_id' => ['sometimes', 'integer', 'exists:event_statuses,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'city' => ['sometimes', 'string', 'max:120'],
            'country' => ['sometimes', 'string', 'size:2'],
            'latitude' => ['sometimes', 'required_with:longitude', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'required_with:latitude', 'numeric', 'between:-180,180'],
            'starts_at' => ['sometimes', 'date'],
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

        if (array_key_exists('latitude', $validated) && array_key_exists('longitude', $validated)) {
            $attributes['coordinates'] = new Point(
                (float) $validated['latitude'],
                (float) $validated['longitude'],
                Srid::WGS84,
            );
        }

        return $attributes;
    }
}
