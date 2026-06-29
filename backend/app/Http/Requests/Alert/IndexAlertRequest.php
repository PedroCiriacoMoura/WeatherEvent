<?php

namespace App\Http\Requests\Alert;

use App\Models\Alert;
use Illuminate\Foundation\Http\FormRequest;

class IndexAlertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Alert::class);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'unread' => ['sometimes', 'boolean'],
            'type_id' => ['sometimes', 'integer', 'exists:alert_types,id'],
            'per_page' => ['sometimes', 'integer', 'between:1,100'],
        ];
    }

    public function onlyUnread(): bool
    {
        return $this->boolean('unread');
    }

    public function typeId(): ?int
    {
        $value = $this->validated('type_id');

        return $value === null ? null : (int) $value;
    }

    public function perPage(): int
    {
        return (int) $this->validated('per_page', 15);
    }
}
