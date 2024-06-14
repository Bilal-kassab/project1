<?php

namespace App\Http\Requests\Room;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class IndexRoomRequest extends FormRequest
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
           'start_date'=>"required|date|after_or_equal:$date",
            'end_date'=>'required|date|after_or_equal:start_date',
        ];
    }
}
