<?php

namespace App\Http\Requests\Bank;

use Illuminate\Foundation\Http\FormRequest;

class RechargeRequest extends FormRequest
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
            'email'=>'required|email|exists:users,email',
            'money'=>'required|numeric|gt:0'
        ];
    }

      /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    // public function messages(): array
    // {
    //     return [
    //         'title.required' => 'A title is required',
    //         'body.required' => 'A message is required',
    //     ];
    // }
}
