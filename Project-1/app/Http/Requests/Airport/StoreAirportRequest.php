<?php

namespace App\Http\Requests\Airport;

use Illuminate\Foundation\Http\FormRequest;

class StoreAirportRequest extends FormRequest
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
          'name'=> 'required|string|unique:airports,name',
          'area_id'=> 'required|numeric|exists:areas,id',
          'images'=> 'array',
          'images.*' => 'image|mimes:jpg,png,jpeg,gif,svg|max:2048',
        ];
    }
}
