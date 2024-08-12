<?php

namespace App\Http\Requests\Place;

use Illuminate\Foundation\Http\FormRequest;

class StorePlaceRequest extends FormRequest
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
            'name'=>'required|string|unique:places',
            'area_id'=>'required|numeric|exists:areas,id',
            'category_ids'=> 'present|array',
            'category_ids.*'=> 'required|numeric|exists:categories,id',
            'place_price'=> 'required|numeric|max:10000',
            'text'=> 'string|max:1000',
            'lat'=> 'string',
            'long'=> 'string',
            'images'=> 'required|array|min:2',
            'images.*' => 'required|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
        ];
    }
}
