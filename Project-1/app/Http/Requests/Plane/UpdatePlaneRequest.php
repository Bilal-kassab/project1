<?php

namespace App\Http\Requests\Plane;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlaneRequest extends FormRequest
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
            'name'=> 'string',
            'number_of_seats'=> 'numeric|gt:10',
            'ticket_price'=> 'numeric|gt:0',
            'visible'=>'boolean'
        ];
    }
}
