<?php

namespace App\Http\Requests\Area;

use Illuminate\Foundation\Http\FormRequest;

class GetAreasForCountryRequest extends FormRequest
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
           'country_id'=>['required','exists:countries,id']
        ];
    }
}
