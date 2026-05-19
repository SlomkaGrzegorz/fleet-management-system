<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Vehicle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Vehicle::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'plate_number' => ['required', 'string', 'max:16', Rule::unique('vehicles', 'plate_number')],
            'make'         => ['required', 'string', 'max:64'],
            'model'        => ['required', 'string', 'max:64'],
            'year'         => ['required', 'integer', 'min:1950', 'max:' . (int) date('Y') + 1],
            'vin'          => ['nullable', 'string', 'size:17', Rule::unique('vehicles', 'vin')],
            'status'       => ['required', Rule::in([
                Vehicle::STATUS_ACTIVE,
                Vehicle::STATUS_IN_SERVICE,
                Vehicle::STATUS_RETIRED,
                Vehicle::STATUS_SOLD,
            ])],
        ];
    }
}
