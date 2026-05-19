<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Cost;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Cost::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'vehicle_id'  => ['required', 'integer', Rule::exists('vehicles', 'id')],
            'event_id'    => ['nullable', 'integer', Rule::exists('events', 'id')],
            'category'    => ['required', Rule::in([
                Cost::CATEGORY_FUEL,
                Cost::CATEGORY_SERVICE,
                Cost::CATEGORY_REPAIR,
                Cost::CATEGORY_INSURANCE,
                Cost::CATEGORY_TAX,
                Cost::CATEGORY_FINE,
                Cost::CATEGORY_PARTS,
                Cost::CATEGORY_OTHER,
            ])],
            'amount'      => ['required', 'numeric', 'min:0.01', 'max:9999999.99'],
            'incurred_at' => ['required', 'date', 'before_or_equal:today'],
            'description' => ['nullable', 'string', 'max:255'],
            'invoice'     => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:8192'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'vehicle_id'  => 'pojazd',
            'category'    => 'kategoria',
            'amount'      => 'kwota',
            'incurred_at' => 'data poniesienia kosztu',
            'invoice'     => 'faktura',
        ];
    }
}
