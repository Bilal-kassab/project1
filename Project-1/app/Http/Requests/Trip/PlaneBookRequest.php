<?php

namespace App\Http\Requests\Trip;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class PlaneBookRequest extends FormRequest
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
           'source_trip_id'=>'required|exists:countries,id',
            'destination_trip_id'=>'required|exists:countries,id',
            'start_date'=>"required|date|after_or_equal:$date",
            'end_date'=>'required|date|after_or_equal:start_date',
            'trip_name'=>'string',
            'number_of_people'=>'required|min:1|numeric',
            'trip_note'=>'string',
            'plane_trip_id'=>'exists:plane_trips,id',
            'plane_trip_away_id'=>'exists:plane_trips,id',
        ];
    }
}
