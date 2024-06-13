<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'name'=>'required|string',
            'email'=>'required|string|email|unique:users',
            'password'=>'required|min:8|confirmed',
            'role_id'=>'required|numeric',
            'image'=> 'image|mimes:jpg,png,jpeg,gif,svg|max:2048',
            'position'=>'numeric|exists:countries,id',
            'phone_number'=>'regex:/[0-9]{10}/|unique:users'
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
    //         'required' => trans('validate.required'),
    //         // 'string' => '',
    //         // 'email'=>'',
    //         // 'unique'=>'',
    //         // 'confirmed'=>'',
    //         // 'min'=>'',
    //         // 'numeric'=>'',
    //         // 'image'=>'',
    //         // 'position.countries'=>'',
    //         // 'regex'=>'',
    //     ];
    // }
}
