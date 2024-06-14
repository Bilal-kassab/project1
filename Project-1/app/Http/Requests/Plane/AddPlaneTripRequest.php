<?php

namespace App\Http\Requests\Plane;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class AddPlaneTripRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $date=Carbon::now()->format('Y-m-d');
        return [
            'plane_id'=> 'required|numeric|exists:planes,id',
            'airport_source_id'=>'required|numeric|exists:airports,id',
            'airport_destination_id'=>'required|numeric|exists:airports,id',
            'current_price'=> 'required|numeric|gt:0',
            'available_seats'=> 'required|numeric|gt:0',
            'flight_date' => "required|date|after_or_equal:$date",
            'landing_date' => 'required|date|after_or_equal:flight_date',
        ];
    }
}
