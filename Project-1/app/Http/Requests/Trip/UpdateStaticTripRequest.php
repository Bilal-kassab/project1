<?php

namespace App\Http\Requests\Trip;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStaticTripRequest extends FormRequest
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
            'trip_name'=>'string',
            'price'=>'numeric',
            //'hotel_id'=>'exists:hotels,id|same:hotel_id_old',
            'number_of_people'=>'min:1|numeric',
            'start_date'=>"date|after_or_equal:$date",
            'end_date'=>'date|after_or_equal:end_date',
            'trip_note'=>'string',
            'places'=>'array|min:1',
            'places.*'=>"exists:places,id",
            'plane_trip'=>"exists:plane_trips,id",
            'plane_trip_away'=>'exists:plane_trips,id',
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
