<?php

namespace App\Http\Requests\Event;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexEventRequest extends FormRequest
{
    public const SORTABLE = ['name', 'starts_at', 'ends_at', 'attendees', 'created_at'];

    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Event::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['sometimes', 'integer', 'exists:event_categories,id'],
            'status_id' => ['sometimes', 'integer', 'exists:event_statuses,id'],
            'city' => ['sometimes', 'string', 'max:120'],
            'country' => ['sometimes', 'string', 'size:2'],
            'is_outdoor' => ['sometimes', 'boolean'],
            'starts_after' => ['sometimes', 'date'],
            'starts_before' => ['sometimes', 'date'],
            'search' => ['sometimes', 'string', 'max:255'],
            'sort' => ['sometimes', 'string', Rule::in(self::SORTABLE)],
            'direction' => ['sometimes', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['sometimes', 'integer', 'between:1,100'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        return $this->safe()->only([
            'category_id',
            'status_id',
            'city',
            'country',
            'is_outdoor',
            'starts_after',
            'starts_before',
            'search',
        ]);
    }

    public function sortField(): string
    {
        return $this->validated('sort', 'starts_at');
    }

    public function sortDirection(): string
    {
        return $this->validated('direction', 'asc');
    }

    public function perPage(): int
    {
        return (int) $this->validated('per_page', 15);
    }
}
