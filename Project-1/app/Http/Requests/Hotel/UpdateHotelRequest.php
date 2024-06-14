<?php

namespace App\Http\Requests\Hotel;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHotelRequest extends FormRequest
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
            'name'=>'required|string|unique:hotels',
            'user_id'=>'required|numeric|exists:users,id',
            'area_id'=>'required|numeric|exists:areas,id',
            'number_rooms'=>'required|numeric|max:1000|min:10',
        ];
    }
}
