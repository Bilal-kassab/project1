<?php

namespace App\Http\Requests\Trip;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class DynamicTripRequest extends FormRequest
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
            'end_date'=>'required|date|after_or_equal:end_date',
            'trip_name'=>'string',
            'number_of_people'=>'required|min:1|numeric',
            'trip_note'=>'string',
            'place_id'=>'array|min:1',
            'place_id.*'=>'required|exists:places,id',
            'plane_trip_id'=>'exists:plane_trips,id',
            'hotel-id'=>'exists:hotels,id',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    // public function messages(): array
    // {
    //     return [
    //         'title.required' => 'A title is required',
    //         'body.required' => 'A message is required',
    //     ];
    // }
}
