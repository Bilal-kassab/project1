<?php

namespace App\Http\Requests\Trip;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDynamicTripRequest extends FormRequest
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
                // 'source_trip_id'=>'required|exists:countries,id',
                // 'destination_trip_id'=>'required|exists:countries,id',
                //'start_date'=>"required|date|after_or_equal:$date",
                'end_date'=>"required|date|after_or_equal:$date",
                'trip_name'=>'string',
                'number_of_people'=>'numeric',
                'trip_note'=>'string',
                'place_ids'=>'required|array|min:1',
                'place_ids.*'=>'required|exists:places,id',
                'plane_trip_id'=>'exists:plane_trips,id',
                'plane_trip_away_id'=>'exists:plane_trips,id',
                'hotel_id'=>'exists:hotels,id',
                'activities'=>'required|array|min:1',
                'activities.*'=>"required|exists:activities,id",
                'count_room_C2'=>'numeric|min:0',
                'count_room_C4'=>'numeric|min:0',
                'count_room_C6'=>'numeric|min:0',
            ];
    }
}
