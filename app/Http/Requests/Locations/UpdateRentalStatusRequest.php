<?php

namespace App\Http\Requests\Locations;

use App\Enums\Locations\RentalStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRentalStatusRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(RentalStatus::class)],
            'actual_date' => ['required', 'date_format:Y-m-d H:i:s'], // Précision pour le calcul au prorata
        ];
    }

    public function authorize(): bool
    {
        return auth()->user()->can('locations.manage');
    }
}
