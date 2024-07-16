<?php

namespace App\Http\Requests\Trip;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class SearchStaticBookRequest extends FormRequest
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
        $date=Carbon::now()->format('Y-m-d');
        return [
            'type'=>'required|string|in:activity,country,place,date',
            'search_variable'=>'string|required_if:type,activity,country,place',
            'first_date'=>"required_if:type,date|date|after_or_equal:$date",
            'second_date'=>"required_if:type,date|date|after:first_date",
        ];
    }
}
