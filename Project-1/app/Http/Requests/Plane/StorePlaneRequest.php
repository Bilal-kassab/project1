<?php

namespace App\Http\Requests\Plane;

use Illuminate\Foundation\Http\FormRequest;

class StorePlaneRequest extends FormRequest
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
            'name'=> 'required|string|unique:planes,name',
            // 'airport_id'=>'required|numeric|exists:airports,id',
            'number_of_seats'=>'required|numeric|gt:10',
            'ticket_price'=> 'required|numeric|gt:0',
            'images'=> 'array',
            'images.*' => 'image|mimes:jpg,png,jpeg,gif,svg|max:2048',
        ];
    }
}
