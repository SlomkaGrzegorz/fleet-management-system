<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Event::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'vehicle_id'  => ['required', 'integer', Rule::exists('vehicles', 'id')],
            'type'        => ['required', Rule::in([
                Event::TYPE_INCIDENT,
                Event::TYPE_REPAIR,
                Event::TYPE_SERVICE,
                Event::TYPE_OTHER,
            ])],
            'event_date'  => ['required', 'date', 'before_or_equal:today'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:event_date'],
            'notes'       => ['required', 'string', 'min:5', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'vehicle_id' => 'pojazd',
            'type'       => 'typ zgłoszenia',
            'event_date' => 'data zdarzenia',
            'notes'      => 'opis',
        ];
    }
}
