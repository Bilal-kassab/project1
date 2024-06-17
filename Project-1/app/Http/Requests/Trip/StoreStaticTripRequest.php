<?php

namespace App\Http\Requests\Trip;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StoreStaticTripRequest extends FormRequest
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
            'hotel_id'=>'required|exists:hotels,id',
            // 'source_trip_id'=>'required|exists:countries,id',
            'destination_trip_id'=>'required|exists:countries,id',
            'trip_name'=>'required|string',
            'ratio'=>'required|decimal:0,1',
            'number_of_people'=>'required|min:3|numeric',
            'trip_capacity'=>'required|numeric',
            // 'start_date'=>"required|exists:plane_trips,flight_date|date|after_or_equal:$date",
            // 'end_date'=>'required|date|after_or_equal:start_date',
            'trip_note'=>'string',
            'places'=>'required|array|min:1',
            'places.*'=>"required|exists:places,id",
            'activities'=>'required|array|min:1',
            'activities.*'=>"required|exists:activities,id",
            'plane_trip'=>"required|exists:plane_trips,id",
            'plane_trip_away'=>'required|exists:plane_trips,id',
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
