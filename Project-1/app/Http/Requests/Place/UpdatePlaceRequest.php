<?php

namespace App\Http\Requests\Place;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlaceRequest extends FormRequest
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
            'name'=>'string|unique:places',
            'area_id'=>'numeric|exists:areas,id',
            'category_ids'=> 'array',
            'category_ids.*'=> 'numeric|exists:categories,id',
            'place_price'=> 'numeric|max:10000',
            'text'=> 'string|max:1000',
        ];
    }
}
