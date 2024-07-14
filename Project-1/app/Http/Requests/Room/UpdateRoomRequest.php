<?php

namespace App\Http\Requests\Room;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoomRequest extends FormRequest
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
        return [
            // 'hotel_id'=>'required|numeric|exists:hotels,id',
            'capacity'=>'required|in:2,4,6',
            'price'=>'required|numeric',
        ];
        // Rule::exists('rooms')->where(function ($query) {
        //     return $query->where('hotel_id', request()->get('hotel_id'));
        //     }),
    }
}
